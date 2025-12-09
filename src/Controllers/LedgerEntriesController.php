<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use DomainException;
use Exception;
use InvalidArgumentException;
use PHPLedger\Domain\Defaults;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\DateParser;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\LedgerEntriesView;
use PHPLedger\Views\ViewFactory;

final class LedgerEntriesController extends AbstractViewController
{
    private Defaults $defaults;
    protected function handle(): void
    {
        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('implicit_flush', '1');
        ob_implicit_flush(true);
        $input_variables_filter = [
            'action' => FILTER_DEFAULT,
            'data_mov' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
            ],
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
            'currencyId' => FILTER_SANITIZE_ENCODED,
            'direction' => FILTER_SANITIZE_NUMBER_INT,
            'remarks' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'filter_entryType' => FILTER_SANITIZE_NUMBER_INT,
            'filter_accountId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_parentId' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateAA' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateMM' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDateDD' => FILTER_SANITIZE_NUMBER_INT,
            'filter_startDate' => FILTER_SANITIZE_ENCODED,
            'filter_endDate' => FILTER_SANITIZE_ENCODED,
            'filter_endDateAA' => FILTER_SANITIZE_NUMBER_INT,
            'filter_endDateMM' => FILTER_SANITIZE_NUMBER_INT,
            'filter_endDateDD' => FILTER_SANITIZE_NUMBER_INT,
            'lang' => FILTER_SANITIZE_ENCODED,
            '_csrf_token' => FILTER_DEFAULT,
        ];
        $filteredInput = [];
        $savedEntryId = null;
        $filteredInput = filter_var_array($this->request->all(), $input_variables_filter, true);
        $this->defaults = $this->app->dataFactory()::defaults();
        $filters = $this->getFilters($filteredInput);
        $success = false;
        $errorMessage = "";
        if ($this->request->method() === 'POST') {
            if (!CSRF::validateToken($this->request->input('_csrf_token'))) {
                http_response_code(400);
                Redirector::to('index.php?action=ledger_entries');
                return;
            }
            try {
                $savedEntryId = $this->handleSave($filteredInput);
                $success = true;
                unset($filters['id']);
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }
        $ledgerFilters[] = ["entryDate" => ["operator" => '>=', "value" => $filters['startDate']]];
        $ledgerFilters[] = ["entryDate" => ["operator" => '<=', "value" => $filters['endDate']]];
        if (($filters['accountId'] ?? null) !== null) {
            $ledgerFilters[] = ['accountId' => ["operator" => '=', "value" => $filters["accountId"]]];
        }
        if (($filters["entryType"] ?? null) !== null) {
            $ledgerFilters[] = ['categoryId' => ["operator" => '=', "value" => $filters["entryType"]]];
        }
        if (($filters["parentId"] ?? null) !== null) {
            $ledgerFilters[] = ["parentId" => ["operator" => "IN", "value" => $filters['parentId']]];
        }

        /**
         * @var LedgerEntry
         */
        $ledgerEntryObject = $this->app->dataFactory()::ledgerentry();
        $balance = $ledgerEntryObject->getBalanceBeforeDate($filters['startDate'], $filters["accountId"]);
        $ledgerEntryList = $ledgerEntryObject->getList($ledgerFilters);
        $editId = is_numeric($filteredInput['id'] ?? '') ? (int)$filteredInput['id'] : 0;
        $editEntry = $ledgerEntryObject->getById($editId);

        // Tipos movimento
        $categoryId = $editId > 0 ? $editEntry->categoryId : $this->defaults->categoryId;
        $entry_viewer = ViewFactory::instance()->entryCategoryView($this->app, $this->app->dataFactory()::entryCategory()::getById($categoryId));
        $entryTypesSelectOptions = $entry_viewer->getSelectFromList($this->app->dataFactory()::entryCategory()::getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'id' => ['operator' => '>', 'value' => '0']
        ]));

        // Moedas
        $currencyId = $editId > 0 ? $editEntry->currencyId : $this->defaults->currencyId;
        $currency = $this->app->dataFactory()::currency();
        $currencyViewer = ViewFactory::instance()->currencyView($this->app, $currency);
        $currencySelectOptions = $currencyViewer->getSelectFromList($this->app->dataFactory()::currency()::getList(), $currencyId);

        // Contas
        $accountId = $editId > 0 ? $editEntry->accountId : $this->defaults->accountId;
        $accountViewer = ViewFactory::instance()->accountView($this->app, $this->app->dataFactory()::account()::getById($accountId));
        $accountSelectOptions = $accountViewer->getSelectFromList($this->app->dataFactory()::account()::getList(['activa' => ['operator' => '=', 'value' => '1']]), $accountId);

        $view = new LedgerEntriesView;
        $view->render($this->app, [
            'action' => $this->request->input('action'),
            'filteredInput' => $filteredInput,
            'savedEntryId' => $savedEntryId,
            'success' => $success,
            'errorMessage' => $errorMessage,
            'filters' => $filters,
            'defaults' => $this->defaults,
            'balance' => $balance,
            'ledgerEntryList' => $ledgerEntryList,
            'accountSelectOptions' => $accountSelectOptions,
            'currencySelectOptions' => $currencySelectOptions,
            'entryTypesSelectOptions' => $entryTypesSelectOptions,
            'editId' => $editId,
            'editEntry' => $editEntry,
        ]);
    }
    public function handleSave(array $input): int
    {
        // 1) parse date
        try {
            $dt = DateParser::parseNamed('data_mov', $input);
        } catch (Exception $e) {
            throw new DomainException($this->app->l10n()->l("invalid_date", $e->getMessage()));
        }
        if (!$dt) {
            throw new DomainException($this->app->l10n()->l("date_required"));
        }

        // 2) grab and validate the other fields
        foreach (['currencyAmount', 'direction', 'categoryId', 'currencyId', 'accountId'] as $fld) {
            if (!isset($input[$fld]) || $input[$fld] === '' || $input[$fld] === false) {
                throw new InvalidArgumentException($this->app->l10n()->l("invalid_parameter", $fld));
            }
        }

        // 3) hydrate and save
        $entry = $this->app->dataFactory()::ledgerentry();
        $entry->entryDate = $dt->format('Y-m-d');
        $entry->id = isset($input['id']) && is_numeric($input['id']) ? (int)$input['id'] : $entry::getNextId();
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
        $username = $this->app->session()->get('user', 'empty');
        if (!empty($username)) {
            $this->defaults = $this->defaults::getByUsername($username) ?? $this->defaults::init();
        } else {
            $this->defaults = $this->defaults::init();
        }
        $this->defaults->categoryId = $entry->categoryId;
        $this->defaults->currencyId = $entry->currencyId;
        $this->defaults->accountId = $entry->accountId;
        $this->defaults->entryDate = $entry->entryDate;
        $this->defaults->direction = $entry->direction;
        $this->defaults->language = $this->app->l10n()->lang();
        $this->defaults->username = $username;
        if (!$this->defaults->update()) {
            throw new DomainException($this->app->l10n()->l("defaults_save_error"));
        }
    }
    private function getFilters(array $input): array
    {
        $returnFilters = [];
        $returnFilters['id'] = (int)$input['id'] ?? 0;
        $returnFilters['startDate'] = $input["filter_startDate"] ?? date("Y-m-01");
        $returnFilters['endDate'] = $input["filter_endDate"] ?? date("Y-m-d");
        $numericFields = ['accountId', 'entryType', 'parentId'];
        foreach ($numericFields as $value) {
            $key = "filter_{$value}";
            $returnFilters[$value] = isset($input[$key]) && is_numeric($input[$key]) ? (int)$input[$key] : null;
        }
        return $returnFilters;
    }
}
