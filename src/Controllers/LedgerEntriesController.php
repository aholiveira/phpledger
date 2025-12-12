<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
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
use PHPLedger\Util\CSRF;
use PHPLedger\Util\DateParser;
use PHPLedger\Util\Html;
use PHPLedger\Util\NumberUtil;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\Templates\LedgerEntriesMainViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesPreloaderTemplate;

final class LedgerEntriesController extends AbstractViewController
{
    private DataObjectFactoryInterface $dataFactory;
    private Defaults $defaults;
    private array $entryCategoryListCache;
    private array $currencyListCache;
    private array $accountListCache;
    private bool $isEditing = false;

    protected function handle(): void
    {
        $lang = $this->app->l10n()->html();
        $pagetitle = $this->app->l10n()->l("ledger_entries");
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
        ob_start();
        $preloaderView = new LedgerEntriesPreloaderTemplate();
        $templateData = [
            'lang' => $lang,
            'pagetitle' => $pagetitle,
            'success' => $success,
            'label' => [
                'notification' => $success ? $this->app->l10n()->l("save_success", $savedEntryId) : $errorMessage,
            ]
        ];
        ob_end_flush();
        $preloaderView->render($templateData);
        $this->populateCaches();
        $labels = [
            'id' => $this->app->l10n()->l('id'),
            'date' => $this->app->l10n()->l('date'),
            'category' => $this->app->l10n()->l('category'),
            'currency' => $this->app->l10n()->l('currency'),
            'account' => $this->app->l10n()->l('account'),
            'dc' => $this->app->l10n()->l('dc'),
            'amount' => $this->app->l10n()->l('amount'),
            'remarks' => $this->app->l10n()->l('remarks'),
            'balance' => $this->app->l10n()->l('balance'),
            'start' => $this->app->l10n()->l('start'),
            'end' => $this->app->l10n()->l('end'),
            'no_filter' => $this->app->l10n()->l('no_filter'),
            'filter' => 'Filtrar',
            'clear_filter' => 'Limpar filtro',
            'previous_balance' => 'Saldo anterior',
            'editlink' => 'Editar',
            'action' => htmlentities('Acções'),
            'save' => 'Gravar',
            'direction' => 'D/C',
            'deposit' => $this->app->l10n()->l('deposit'),
            'withdrawal' => $this->app->l10n()->l('withdraw')
        ];

        $filterFormData = $this->prepareFilterFormData($filters);
        $ledgerFilters = $this->getLedgerFilters($filters);

        /**
         * @var LedgerEntry
         */
        $ledgerEntryObject = $this->dataFactory->ledgerentry();
        $startBalance = $ledgerEntryObject->getBalanceBeforeDate($filters['startDate'], $filters["accountId"]);
        $ledgerEntryRows = $this->prepareLedgerEntryRows($ledgerEntryObject, $ledgerFilters, $filters, $startBalance);
        if (empty($ledgerEntryRows)) {
            $formBalance = $startBalance;
        } else {
            $editId = $filters['editId'] ?? 0;
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
                'l10n' => $this->app->l10n(),
                'isAdmin' => $this->app->session()->get('isAdmin', false),
                'action' => $this->request->input('action'),
                'isEditing' => $this->isEditing,
                'editId' => $filters['editId'],
                'filteredInput' => $filters,
                'success' => $success,
                'errorMessage' => $errorMessage,
                'filters' => $filters,
                'defaults' => $this->defaults,
                'startBalance' => $startBalance,
                'transactionsInPeriod' => $this->app->l10n()->l('transactions_in_period', count($ledgerEntryRows)),
                'ledgerEntryRows' => $ledgerEntryRows ?? [],
                'labels' => $labels,
                'formData' => $this->prepareFormData($ledgerEntryObject, $filters, (float)$formBalance) ?? [],
                'filterFormData' => $filterFormData,
                'app' => $this->app,
            ]
        );
        $view = new LedgerEntriesMainViewTemplate;
        $view->render($templateData);
    }
    private function prepareFilterFormData(array $filters): array
    {
        $filterFormData = [];
        $filterFormData['accounts'] = $this->prepareAccountRows($filters['accountId'] ?? 0);
        $filterFormData['entryCategory'] = $this->prepareEntryCategoryRows($filters['categoryId'] ?? 0);
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $filters['startDate']) ?: new DateTimeImmutable(date('Y-m-01'));
        $endDate = DateTimeImmutable::createFromFormat('Y-m-d', $filters['endDate']) ?: new DateTimeImmutable(date('Y-m-d'));
        $filterFormData['startDate'] = $startDate->format("Y-m-d");
        $filterFormData['startDateAA'] = Html::yearOptions($startDate->format("Y"));
        $filterFormData['startDateMM'] = Html::monthOptions($startDate->format("m"));
        $filterFormData['startDateDD'] = Html::dayOptions($startDate->format("d"));
        $filterFormData['endDate'] = $endDate->format("Y-m-d");
        $filterFormData['endDateAA'] = Html::yearOptions($endDate->format("Y"));
        $filterFormData['endDateMM'] = Html::monthOptions($endDate->format("m"));
        $filterFormData['endDateDD'] = Html::dayOptions($endDate->format("d"));
        return $filterFormData;
    }
    private function populateCaches(): void
    {
        $this->accountListCache = $this->dataFactory->account()->getList(['activa' => ['operator' => '=', 'value' => '1']]);
        $this->currencyListCache = $this->dataFactory->currency()->getList();
        $this->entryCategoryListCache = $this->dataFactory->entryCategory()->getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'id' => ['operator' => '>', 'value' => '0']
        ]);
    }
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
        $deposit = $this->app->l10n()->l('deposit');
        $withdrawal = $this->app->l10n()->l('withdraw');
        foreach ($ledgerEntryList as $row) {
            $balance += ($row->direction * $row->currencyAmount);
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
                    'remarks' => $row->remarks,
                    'balance' => NumberUtil::normalize($balance)
                ],
                'href' => [
                    'editlink' => "{$baseLink}{$idQuery}",
                    'category' => "{$baseLink}{$categoryQuery}",
                    'account' => "{$baseLink}{$accountQuery}"
                ],
                'title' => [
                    'editlink' => "{$this->app->l10n()->l('click_to_edit')}&#10;{$this->app->l10n()->l('modified_by_at',$row->username,$row->updatedAt)}",
                    'category' => "Filtrar lista para esta categoria",
                    'account' => "Filtrar lista para esta conta",
                ]
            ];
        }
        return $rows;
    }
    private function prepareFormData(LedgerEntry $ledgerEntryObject, array $filters, float $balance): array
    {
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
                    'text' => $this->app->l10n()->l('deposit'),
                    'value' => 1,
                    'selected' => $editId > 0 ? ($editEntry->direction === 1) : false,
                ],
                [
                    'text' => $this->app->l10n()->l('withdrawal'),
                    'value' => -1,
                    'selected' => $editId > 0 ? ($editEntry->direction === -1) : true,
                ],
            ],
            'amount' => $editId > 0 ? $editEntry->currencyAmount : 0,
            'remarks' => htmlspecialchars($editId > 0 ? $editEntry->remarks : ''),
            'balance' => NumberUtil::normalize($balance),
        ];
    }
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
    private function makeEntryCategoryRow(EntryCategory $row, int $selectedId): array
    {
        return [
            'value' => $row->id,
            'text' => $row->description,
            'parentId' => $row->parentId,
            'selected' => $row->id === $selectedId
        ];
    }
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
    private function processRequest(RequestInterface $request, array $filteredInput): array
    {
        $savedEntryId = null;
        $success = false;
        $errorMessage = "";
        if ($request->method() === 'POST') {
            if (!CSRF::validateToken($request->input('_csrf_token'))) {
                http_response_code(400);
                Redirector::to('index.php?action=ledger_entries');
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
        if ($request->method() === 'GET' && (int)$request->input('editId', 0) !== 0) {
            $this->isEditing = true;
        }
        return [$savedEntryId, $success, $errorMessage];
    }
    private function processInput(array $inputData): array
    {
        $dateFilter = [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
        ];
        $input_variables_filter = [
            'action' => FILTER_DEFAULT,
            'data_mov' => $dateFilter,
            'data_movAA' => FILTER_SANITIZE_NUMBER_INT,
            'data_movMM' => FILTER_SANITIZE_NUMBER_INT,
            'data_movDD' => FILTER_SANITIZE_NUMBER_INT,
            'id' => FILTER_SANITIZE_NUMBER_INT,
            'accountId' => FILTER_SANITIZE_NUMBER_INT,
            'categoryId' => FILTER_SANITIZE_NUMBER_INT,
            'currencyAmount' => [
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
            ],
            'currencyId' => FILTER_SANITIZE_NUMBER_INT,
            'direction' => FILTER_SANITIZE_NUMBER_INT,
            'remarks' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'filter_editId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_entryType' => FILTER_SANITIZE_NUMBER_INT,
            'filter_accountId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateAA' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateMM' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateDD' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDate' => $dateFilter,
            'filter_endDate' => $dateFilter,
            'filter_endDateAA' => FILTER_SANITIZE_NUMBER_INT,
            'filter_endDateMM' => FILTER_SANITIZE_NUMBER_INT,
            'filter_endDateDD' => FILTER_SANITIZE_NUMBER_INT,
            'filters' => [
                'filter' => FILTER_CALLBACK,
                'options' => function ($v) {
                    if (!is_string($v)) {
                        return null;
                    }
                    $decoded = json_decode($v, true);
                    return is_array($decoded) ? $decoded : null;
                }
            ],
            'lang' => FILTER_SANITIZE_ENCODED,
            '_csrf_token' => FILTER_DEFAULT,
        ];
        return filter_var_array($inputData, $input_variables_filter, true);
    }
    private function handleSave(array $input): int
    {
        $dt = $this->validateDateFieldFromInput('data_mov', $input, true);
        foreach (['currencyAmount', 'direction', 'categoryId', 'currencyId', 'accountId'] as $fld) {
            if (!isset($input[$fld]) || $input[$fld] === '' || $input[$fld] === false) {
                throw new InvalidArgumentException($this->app->l10n()->l("invalid_parameter", $fld));
            }
        }
        $entry = $this->dataFactory->ledgerentry();
        $entry->entryDate = $dt->format('Y-m-d');
        $entry->id = isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0 ? (int)$input['id'] : $entry->getNextId();
        $entry->currencyAmount = (float) $input['currencyAmount'];
        $entry->direction = (int) $input['direction'];
        $entry->euroAmount = $entry->direction * $entry->currencyAmount;
        $entry->categoryId = (int) $input['categoryId'];
        $entry->currencyId = $input['currencyId'];
        $entry->accountId = (int) $input['accountId'];
        $entry->remarks = $input['remarks'];
        $entry->username = $this->app->session()->get('user', 'empty');
        if (!$entry->update()) {
            throw new DomainException($this->app->l10n()->l("ledger_save_error"));
        }
        $this->storeDefaults($entry);
        return $entry->id;
    }
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
        $filters['endDate'] = $end ? $end->format('Y-m-d') : date('Y-m-d');
        return $filters;
    }
    private function validateDateFieldFromInput(string $fieldName, array $input, bool $throw = false)
    {
        try {
            $dt = DateParser::parseNamed($fieldName, $input);
        } catch (Exception $e) {
            if ($throw) {
                throw new DomainException($this->app->l10n()->l("invalid_date", $e->getMessage()));
            } else {
                return null;
            }
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
