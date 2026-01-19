<?php

/**
 * Controller for handling ledger entries
 *
 * Handles all operations related to ledger entries, including displaying, filtering,
 * exporting, and saving ledger entries. Integrates with data factories, UI templates,
 * and localization services.
 *
 * @author Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 */

namespace PHPLedger\Controllers;

use DateTimeImmutable;
use DomainException;
use Exception;
use InvalidArgumentException;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\RequestInterface;
use PHPLedger\Domain\Defaults;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Util\CsvBuilder;
use PHPLedger\Util\DateParser;
use PHPLedger\Util\NumberUtil;
use PHPLedger\Views\Templates\LedgerEntriesFilterViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesFormViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesMainViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesPreloaderTemplate;
use PHPLedger\Views\Templates\LedgerEntriesRowViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesTableViewTemplate;

final class LedgerEntriesController extends AbstractViewController
{
    private DataObjectFactoryInterface $dataFactory;
    private Defaults $defaults;
    private array $entryCategoryListCache;
    private array $currencyListCache;
    private array $accountListCache;
    private bool $isEditing = false;

    /**
     * Main request handler for ledger entries.
     *
     * Initializes data, applies filters, handles export requests, populates caches,
     * prepares UI templates, and renders the main ledger entries view.
     *
     * @return void
     */
    protected function handle(): void
    {
        $l10n = $this->app->l10n();
        $pagetitle = $l10n->l("ledger_entries");
        $this->dataFactory = $this->app->dataFactory();
        $this->defaults = $this->dataFactory->defaults();
        $username = $this->app->session()->get('user', 'empty');
        if (!empty($username)) {
            $this->defaults = $this->defaults::getByUsername($username) ?? $this->defaults::init();
        } else {
            $this->defaults = $this->defaults::init();
        }
        $filteredInput = $this->processInput($this->request->all());
        $filters = $this->getFilters($filteredInput);
        [$savedEntryId, $success, $errorMessage] = $this->processRequest($this->request, $filteredInput);
        if ($savedEntryId !== null && $success) {
            $filters['editId'] = 0;
        }
        $query = $this->request->all();
        $query['export'] = 'csv';
        $downloadUrl = 'index.php?' . http_build_query($query);
        $export = strtolower((string)$this->request->input('export', ''));
        if ($export === 'csv') {
            $this->ledgerEntriesDownload($filters);
            return;
        }
        ob_start();
        $preloaderView = new LedgerEntriesPreloaderTemplate();
        $templateData = array_merge($this->uiData, [
            'pagetitle' => $pagetitle,
            'success' => $success
        ]);
        $templateData['label']['notification'] = $success ? $l10n->l("save_success", $savedEntryId) : $errorMessage;
        ob_end_flush();
        $preloaderView->render($templateData);
        $this->populateCaches();
        $filterFormData = $this->prepareFilterFormData($filters);
        $ledgerFilters = $this->getLedgerFilters($filters);

        /**
         * @var LedgerEntry
         */
        $ledgerEntryObject = $this->dataFactory->ledgerentry();
        $startBalance = $ledgerEntryObject->getBalanceBeforeDate($filters['startDate'], $filters["accountId"]) ?? 0;
        $ledgerEntryRows = $this->prepareLedgerEntryRows($ledgerEntryObject, $ledgerFilters, $filters, $startBalance);
        if (empty($ledgerEntryRows)) {
            $formBalance = $startBalance;
        } else {
            $editId = $filters['editId'];
            if ($editId > 0 && isset($ledgerEntryRows[$editId]['text']['balance'])) {
                $formBalance = (float) str_replace(",", "", $ledgerEntryRows[$editId]['text']['balance']);
            } else {
                $last = end($ledgerEntryRows);
                $formBalance = (float) str_replace(",", "", $last['text']['balance']);
            }
        }
        $templateData = array_merge(
            $templateData,
            [
                'downloadUrl' => $downloadUrl,
                'isEditing' => $this->isEditing,
                'editId' => $filters['editId'],
                'filteredInput' => $filters,
                'filters' => $filters,
                'defaults' => $this->defaults,
                'startBalance' => $startBalance,
                'transactionsInPeriod' => $l10n->l('transactions_in_period', count($ledgerEntryRows)),
                'ledgerEntryRows' => $ledgerEntryRows ?? [],
                'formData' => $this->prepareFormData($ledgerEntryObject, $filters, (float)$formBalance) ?? [],
                'filterFormData' => $filterFormData,
                'csrf' => $this->app->csrf()->inputField(),
                'filterViewTemplate' => new LedgerEntriesFilterViewTemplate,
                'tableViewTemplate' => new LedgerEntriesTableViewTemplate,
                'rowViewTemplate' => new LedgerEntriesRowViewTemplate,
                'formViewTemplate' => new LedgerEntriesFormViewTemplate,
            ]
        );
        $view = new LedgerEntriesMainViewTemplate;
        $view->render($templateData);
    }

    /**
     * Exports ledger entries as a CSV file based on the given filters.
     *
     * @param array $filters Associative array of filters to apply for the export.
     * @return void
     */
    private function ledgerEntriesDownload(array $filters): void
    {
        $ledgerEntry = $this->dataFactory->ledgerentry();
        $ledgerFilters = $this->getLedgerFilters($filters);
        $dataRows = $ledgerEntry->getList($ledgerFilters);
        $l10n = $this->app->l10n();
        $headers = [
            $l10n->l('id'),
            $l10n->l('date'),
            $l10n->l('category'),
            $l10n->l('account'),
            $l10n->l('currency'),
            $l10n->l('direction'),
            $l10n->l('amount'),
            $l10n->l('exchangeRate'),
            $l10n->l('euro'),
            $l10n->l('remarks')
        ];
        $rows = [];
        $withdrawal = $l10n->l('withdraw');
        $deposit = $l10n->l('deposit');
        foreach ($dataRows as $r) {
            $rows[] = [
                $r->id,
                $r->entryDate,
                $r->category->description,
                $r->account->name,
                $r->currency->description,
                $r->direction === 1 ? $deposit : $withdrawal,
                $r->exchangeRate,
                $r->currencyAmount,
                $r->euroAmount,
                $r->remarks
            ];
        }
        $this->app->fileResponseSender()->csv(CsvBuilder::build($headers, $rows), 'ledger_entries.csv');
    }

    /**
     * Prepares form data for the ledger entries filter form.
     *
     * @param array $filters Associative array of filters applied.
     * @return array Structured form data including accounts, categories, start and end dates.
     */
    private function prepareFilterFormData(array $filters): array
    {
        $filterFormData = [];
        $filterFormData['accounts'] = $this->prepareAccountRows($filters['accountId'] ?? 0);
        $filterFormData['entryCategory'] = $this->prepareEntryCategoryRows($filters['categoryId'] ?? 0);
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $filters['startDate']) ?: new DateTimeImmutable(date('Y-m-01'));
        $endDate = DateTimeImmutable::createFromFormat('Y-m-d', $filters['endDate']) ?: new DateTimeImmutable(date('Y-m-d'));
        $filterFormData['startDate'] = $startDate->format("Y-m-d");
        $filterFormData['endDate'] = $endDate->format("Y-m-d");
        return $filterFormData;
    }

    /**
     * Populates cached lists of accounts, currencies, and entry categories.
     *
     * This is used to reduce repeated database queries during ledger entry rendering.
     *
     * @return void
     */
    private function populateCaches(): void
    {
        $this->accountListCache = $this->dataFactory->account()->getList(['active' => ['operator' => '=', 'value' => '1']]);
        $this->currencyListCache = $this->dataFactory->currency()->getList();
        $this->entryCategoryListCache = $this->dataFactory->entryCategory()->getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'id' => ['operator' => '>', 'value' => '0']
        ]);
    }

    /**
     * Prepares ledger entry rows for display in the table view.
     *
     * @param LedgerEntry $ledgerEntryObject LedgerEntry domain object for data retrieval.
     * @param array $ledgerFilters Filters to apply when fetching ledger entries.
     * @param array $filters Filter values used for generating links and context.
     * @param float $startBalance Initial balance before the filtered period.
     * @return array Structured array of ledger entries including text, href, and title data.
     */
    private function prepareLedgerEntryRows(LedgerEntry $ledgerEntryObject, array $ledgerFilters, array $filters, float $startBalance): array
    {
        $ledgerEntryList = $ledgerEntryObject->getList($ledgerFilters);
        $baseLink = "index.php?action=ledger_entries&";
        $filtersArray = array_combine(
            array_map(fn($k) => "filter_$k", array_keys($filters)),
            array_values($filters)
        );
        $balance = $startBalance;
        $rows = [];
        $l10n = $this->app->l10n();
        $deposit = $l10n->l('deposit');
        $withdrawal = $l10n->l('withdraw');
        foreach ($ledgerEntryList as $row) {
            $balance += $row->euroAmount;
            $idQuery = http_build_query(array_merge($filtersArray, ['filter_editId' => $row->id]));
            $categoryQuery = http_build_query(array_merge($filtersArray, ['filter_entryType' => $row->categoryId]));
            $accountQuery = http_build_query(array_merge($filtersArray, ['filter_accountId' => $row->accountId]));
            $rows[$row->id] = [
                'text' => [
                    'editlink' => 'index.php?action=ledger_entries',
                    'id' => $row->id,
                    'date' => $row->entryDate,
                    'category' => ($row->category->parentId > 0 ? ($row->category->parentDescription ?? '') . "&#8594;" : "") . $row->category->description,
                    'currency' => $row->currency->description,
                    'account' => $row->account->name,
                    'direction' => (int)$row->direction === 1 ? $deposit : $withdrawal,
                    'amount' => NumberUtil::normalize($row->currencyAmount),
                    'exchangeRate' => NumberUtil::normalize($row->exchangeRate, 8),
                    'euroAmount' => NumberUtil::normalize($row->euroAmount),
                    'remarks' => $row->remarks,
                    'balance' => NumberUtil::normalize($balance)
                ],
                'href' => [
                    'editlink' => "{$baseLink}{$idQuery}",
                    'category' => "{$baseLink}{$categoryQuery}",
                    'account' => "{$baseLink}{$accountQuery}"
                ],
                'title' => [
                    'editlink' => "{$l10n->l('click_to_edit')}&#10;{$l10n->l('modified_by_at',$row->username,$row->updatedAt)}",
                    'category' => "Filtrar lista para esta categoria",
                    'account' => "Filtrar lista para esta conta",
                ]
            ];
        }
        return $rows;
    }

    /**
     * Prepares data for the ledger entry edit/create form.
     *
     * @param LedgerEntry $ledgerEntryObject LedgerEntry domain object for retrieving the entry by ID.
     * @param array $filters Current filter context including editId.
     * @param float $balance Current balance for the entry.
     * @return array Form data including categories, currencies, accounts, direction, amount, remarks, and balance.
     */
    private function prepareFormData(LedgerEntry $ledgerEntryObject, array $filters, float $balance): array
    {
        $l10n = $this->app->l10n();
        $editId = is_numeric($filters['editId'] ?? '') ? (int)$filters['editId'] : 0;
        $editEntry = $ledgerEntryObject->getById($editId);
        $selectedEntryCategoryId = (int)($editId > 0 ? $editEntry->categoryId : $this->defaults->categoryId);
        $selectedCurrencyId = $editId > 0 ? $editEntry->currencyId : $this->defaults->currencyId;
        $selectedAccountId = $editId > 0 ? $editEntry->accountId : $this->defaults->accountId;
        return [
            'id' => $editId,
            'date' => $editId > 0 ? $editEntry->entryDate : $this->defaults->entryDate,
            'entryCategoryRows' => $this->prepareEntryCategoryRows($selectedEntryCategoryId),
            'currencyRows' => $this->prepareCurrencyRows((int)$selectedCurrencyId),
            'accountRows' => $this->prepareAccountRows((int)$selectedAccountId),
            'direction' => [
                [
                    'text' => $l10n->l('deposit'),
                    'value' => 1,
                    'selected' => $editId > 0 ? ($editEntry->direction === 1) : ($this->defaults->direction === 1),
                ],
                [
                    'text' => $l10n->l('withdraw'),
                    'value' => -1,
                    'selected' => $editId > 0 ? ($editEntry->direction === -1) : ($this->defaults->direction === -1),
                ],
            ],
            'amount' => $editId > 0 ? $editEntry->currencyAmount : 0.0,
            'exchangeRate' => $editId > 0 ? $editEntry->exchangeRate : 1.00000000,
            'euroAmount' => $editId > 0 ? $editEntry->euroAmount : 0.00000000,
            'remarks' => $editId > 0 ? $editEntry->remarks : '',
            'balance' => NumberUtil::normalize($balance),
        ];
    }

    /**
     * Prepares an array of accounts for form selection.
     *
     * @param int $selectedId ID of the account that should be marked as selected.
     * @return array Array of account rows including value, text, and selected status.
     */
    private function prepareAccountRows(int $selectedId): array
    {
        $accountRows = [];
        /**
         * @var \PHPLedger\Domain\Account $row
         */
        foreach ($this->accountListCache as $row) {
            $accountRows[] = [
                'value' => $row->id,
                'text' => $row->name,
                'selected' => $row->id === $selectedId,
            ];
        }
        return $accountRows;
    }

    /**
     * Prepares an array of currencies for form selection.
     *
     * @param int $selectedId ID of the currency that should be marked as selected.
     * @return array Array of currency rows including value, text, and selected status.
     */
    private function prepareCurrencyRows(int $selectedId): array
    {
        $currencyRows = [];
        /**
         * @var \PHPLedger\Domain\Currency $row
         */
        foreach ($this->currencyListCache as $row) {
            $currencyRows[] = [
                'value' => $row->id,
                'text' => $row->description,
                'selected' => $row->id === $selectedId,
            ];
        }
        return $currencyRows;
    }

    /**
     * Constructs a single ledger entry category row for selection.
     *
     * @param EntryCategory $row EntryCategory object.
     * @param int $selectedId ID of the category that should be marked as selected.
     * @return array Array containing value, text, parentId, and selected status.
     */
    private function makeEntryCategoryRow(EntryCategory $row, int $selectedId): array
    {
        return [
            'value' => $row->id,
            'text' => $row->description,
            'parentId' => $row->parentId,
            'selected' => $row->id === $selectedId
        ];
    }

    /**
     * Prepares an array of ledger entry categories for form selection, including children.
     *
     * @param int $selectedEntryCategoryId ID of the category that should be marked as selected.
     * @return array Array of category rows including value, text, parentId, and selected status.
     */
    private function prepareEntryCategoryRows(int $selectedEntryCategoryId): array
    {
        $entryCategoryRows = [];
        foreach ($this->entryCategoryListCache as $row) {
            $entryCategoryRows[] = $this->makeEntryCategoryRow($row, $selectedEntryCategoryId);
            foreach ($row->children ?? [] as $child) {
                $entryCategoryRows[] = $this->makeEntryCategoryRow($child, $selectedEntryCategoryId);
            }
        }
        return $entryCategoryRows;
    }

    /**
     * Builds an array of filters suitable for querying ledger entries.
     *
     * @param array $filters Associative array containing filter values like startDate, endDate, accountId, and entryType.
     * @return array Array of filters with operators and values for ledger entry queries.
     */
    private function getLedgerFilters(array $filters): array
    {
        $ledgerFilters = [];
        $ledgerFilters[] = ["entryDate" => ["operator" => '>=', "value" => $filters['startDate']]];
        $ledgerFilters[] = ["entryDate" => ["operator" => '<=', "value" => $filters['endDate']]];
        if (($filters['accountId'] ?? null) !== null) {
            $ledgerFilters[] = ['accountId' => ["operator" => '=', "value" => $filters["accountId"]]];
        }
        if (($filters["entryType"] ?? null) !== null) {
            $ledgerFilters[] = ['categoryId' => ["operator" => '=', "value" => $filters["entryType"]]];
        }
        return $ledgerFilters;
    }

    /**
     * Handles a request for ledger entry operations, including saving and editing.
     *
     * @param RequestInterface $request The request object containing POST or GET data.
     * @param array $filteredInput Input data filtered and sanitized.
     * @return array Array containing saved entry ID (or null), success status, and error message.
     */
    private function processRequest(RequestInterface $request, array $filteredInput): array
    {
        $savedEntryId = null;
        $success = false;
        $errorMessage = "";
        if ($request->isPost()) {
            if (!$this->app->csrf()->validateToken($request->input('_csrf_token'))) {
                http_response_code(400);
                $errorMessage = "CSRF Validation";
                $success = false;
                return [$savedEntryId, $success, $errorMessage];
            }
            try {
                $savedEntryId = $this->handleSave($filteredInput);
                $success = true;
                $this->isEditing = false;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }
        if ($request->isGet() && (int)$request->input('editId', 0) !== 0) {
            $this->isEditing = true;
        }
        return [$savedEntryId, $success, $errorMessage];
    }

    /**
     * Processes and sanitizes input data for ledger entry operations.
     *
     * @param array $inputData Raw input data from request.
     * @return array Filtered and sanitized input data array.
     */
    private function processInput(array $inputData): array
    {
        $dateFilter = [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
        ];
        $input_variables_filter = [
            'action' => FILTER_DEFAULT,
            'data_mov' => $dateFilter,
            'id' => FILTER_SANITIZE_NUMBER_INT,
            'accountId' => FILTER_SANITIZE_NUMBER_INT,
            'categoryId' => FILTER_SANITIZE_NUMBER_INT,
            'currencyAmount' => [
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
            ],
            'exchangeRate' => [
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
            ],
            'currencyId' => FILTER_SANITIZE_NUMBER_INT,
            'direction' => FILTER_SANITIZE_NUMBER_INT,
            'remarks' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'filter_editId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_entryType' => FILTER_SANITIZE_NUMBER_INT,
            'filter_accountId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDate' => $dateFilter,
            'filter_endDate' => $dateFilter,
            'lang' => FILTER_SANITIZE_ENCODED,
            '_csrf_token' => FILTER_DEFAULT,
        ];
        return filter_var_array($inputData, $input_variables_filter, true);
    }

    /**
     * Save a ledger entry or update an existing one from input data.
     *
     * Validates required fields and the CSRF token, then persists the entry
     * and updates user defaults accordingly.
     *
     * @param array $input The input data for the ledger entry.
     * @return int The ID of the saved ledger entry.
     * @throws PHPLedgerException If the user lacks write permissions.
     * @throws InvalidArgumentException If required input fields are missing or invalid.
     * @throws DomainException If saving the ledger entry fails.
     */
    private function handleSave(array $input): int
    {
        $l10n = $this->app->l10n();
        $userName = $this->currentUser?->getProperty('userName', '');
        if (!$this->permissions?->canWrite()) {
            throw new PHPLedgerException('No permissions');
        }
        $dt = $this->validateDateFieldFromInput('data_mov', $input, true);
        foreach (['currencyAmount', 'direction', 'categoryId', 'currencyId', 'accountId'] as $fld) {
            if (!isset($input[$fld]) || $input[$fld] === '' || $input[$fld] === false) {
                throw new InvalidArgumentException($l10n->l("invalid_parameter", $fld));
            }
        }
        $entry = $this->dataFactory->ledgerentry();
        $entry->entryDate = $dt->format('Y-m-d');
        $entry->id = isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0 ? (int)$input['id'] : null;
        $entry->currencyAmount = (float) $input['currencyAmount'];
        $entry->direction = (int) $input['direction'];
        $entry->exchangeRate = (float)$input['exchangeRate'];
        $entry->euroAmount = $entry->direction * $entry->currencyAmount * $entry->exchangeRate;
        $entry->categoryId = (int) $input['categoryId'];
        $entry->currencyId = $input['currencyId'];
        $entry->accountId = (int) $input['accountId'];
        $entry->remarks = $input['remarks'] ?? '';
        $entry->username = $userName;
        if (!$entry->update()) {
            throw new DomainException($l10n->l("ledger_save_error"));
        }
        $this->app->logger()->info("Ledger entry [{$entry->id}] save by user [{$userName}]");
        $this->storeDefaults($entry);
        return $entry->id;
    }

    /**
     * Store the current ledger entry as the user's default settings.
     *
     * Updates category, currency, account, date, direction, language, and username
     * in the defaults object and persists it.
     *
     * @param LedgerEntry $entry The ledger entry whose values will be stored as defaults.
     * @throws DomainException If saving the defaults fails.
     */
    private function storeDefaults(LedgerEntry $entry)
    {
        $this->defaults->categoryId = $entry->categoryId;
        $this->defaults->currencyId = $entry->currencyId;
        $this->defaults->accountId = $entry->accountId;
        $this->defaults->entryDate = $entry->entryDate;
        $this->defaults->direction = $entry->direction;
        $this->defaults->language = $this->app->l10n()->lang();
        $this->defaults->username = $entry->username;
        if (!$this->defaults->update()) {
            throw new DomainException($this->app->l10n()->l("defaults_save_error"));
        }
    }
    /**
     * Extract and normalize filter values from input data.
     *
     * Converts numeric filters to integers and validates start and end dates.
     * If no filters are provided, defaults are used.
     *
     * @param array $input Input data array.
     * @return array Normalized filter array including 'startDate' and 'endDate'.
     */
    private function getFilters(array $input): array
    {
        if (isset($input['filters'])) {
            $filters = is_array($input['filters']) ? $input['filters'] : [];
        } else {
            $filters = [];
            $numericFields = ['accountId', 'entryType', 'editId'];
            foreach ($numericFields as $value) {
                $key = "filter_{$value}";
                $filters[$value] = isset($input[$key]) && is_numeric($input[$key]) ? (int)$input[$key] : null;
            }
            $filters['startDate'] = $input['filter_startDate'] ?? null;
            $filters['endDate']   = $input['filter_endDate'] ?? null;
        }

        $start = $this->validateDateFieldFromInput('startDate', $filters);
        $end   = $this->validateDateFieldFromInput('endDate', $filters);

        $filters['startDate'] = $start ? $start->format('Y-m-d') : date('Y-m-01');
        $filters['endDate']   = $end ? $end->format('Y-m-d') : date('Y-m-d');

        return $filters;
    }

    /**
     * Validate and parse a date field from input.
     *
     * @param string $fieldName Name of the input field to validate.
     * @param array $input Input array containing the date field.
     * @param bool $throw Whether to throw an exception if the date is invalid.
     * @return DateTimeImmutable|null The parsed date or null if invalid and $throw is false.
     * @throws DomainException If $throw is true and date is invalid or missing.
     */
    private function validateDateFieldFromInput(string $fieldName, array $input, bool $throw = false)
    {
        try {
            $dt = DateParser::parseNamed($fieldName, $input);
        } catch (Exception $e) {
            if ($throw) {
                throw new DomainException($this->app->l10n()->l("invalid_date", $e->getMessage()));
            }
            return null;
        }

        if (!$dt) {
            if ($throw) {
                throw new DomainException($this->app->l10n()->l("date_required"));
            }
            $dt = null;
        }

        return $dt;
    }
}
