<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\LedgerEntriesFilterViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesFormViewTemplate;

beforeEach(function () {
    $this->filterTemplate = new LedgerEntriesFilterViewTemplate();
    $this->formTemplate = new LedgerEntriesFormViewTemplate();

    $this->filterData = [
        'lang' => 'en',
        'label' => [
            'start' => 'Start Date',
            'end' => 'End Date',
            'account' => 'Account',
            'category' => 'Category',
            'no_filter' => 'No Filter',
            'filter' => 'Filter',
            'clear_filter' => 'Clear Filter'
        ],
        'filters' => [],
        'filterFormData' => [
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
            'accounts' => [
                ['value' => '1', 'text' => 'Cash', 'parentId' => 0, 'selected' => false],
                ['value' => '2', 'text' => 'Bank', 'parentId' => 0, 'selected' => false]
            ],
            'entryCategory' => [
                ['value' => '10', 'text' => 'Sales', 'parentId' => 0, 'selected' => false],
                ['value' => '20', 'text' => 'Expenses', 'parentId' => 0, 'selected' => false]
            ]
        ]
    ];

    $this->formData = [
        'lang' => 'en',
        'label' => [
            'actions' => 'Actions',
            'id' => 'ID',
            'date' => 'Date',
            'category' => 'Category',
            'currency' => 'Currency',
            'account' => 'Account',
            'dc' => 'Direction',
            'amount' => 'Amount',
            'exchangeRate' => 'exchangeRate',
            'euro' => 'euro',
            'remarks' => 'Remarks',
            'balance' => 'Balance',
            'save' => 'Save'
        ],
        'csrf' => '<input type="hidden" name="csrf_token" value="123">',
        'formData' => [
            'id' => 0,
            'date' => '2025-01-01',
            'entryCategoryRows' => [
                ['value' => '10', 'text' => 'Sales', 'parentId' => 0, 'selected' => false]
            ],
            'currencyRows' => [
                ['value' => '1', 'text' => 'USD', 'parentId' => 0, 'selected' => false]
            ],
            'accountRows' => [
                ['value' => '1', 'text' => 'Cash', 'parentId' => 0, 'selected' => false]
            ],
            'direction' => [
                ['value' => 'D', 'text' => 'Debit', 'parentId' => 0, 'selected' => false],
                ['value' => 'C', 'text' => 'Credit', 'parentId' => 0, 'selected' => false]
            ],
            'amount' => 100,
            'exchangeRate' => 1,
            'euroAmount' => 100,
            'remarks' => 'Test',
            'balance' => 1000
        ],
        'filters' => []
    ];
});

it('renders LedgerEntriesFilterViewTemplate with correct form fields', function () {
    ob_start();
    $this->filterTemplate->render($this->filterData);
    $output = ob_get_clean();

    expect($output)->toContain('<form id="datefilter"');
    expect($output)->toContain('<input name="action" value="ledger_entries" type="hidden">');
    expect($output)->toContain('<input name="lang" value="en" type="hidden">');
    expect($output)->toContain('<input type="date" id="filter_startDate" name="filter_startDate"');
    expect($output)->toContain('<input type="date" id="filter_endDate" name="filter_endDate"');
    expect($output)->toContain('<select name="filter_accountId"');
    expect($output)->toContain('<option value="1" >Cash</option>');
    expect($output)->toContain('<option value="2" >Bank</option>');
    expect($output)->toContain('<select name="filter_entryType"');
    expect($output)->toContain('<option value="10" >Sales</option>');
    expect($output)->toContain('<option value="20" >Expenses</option>');
    expect($output)->toContain('<input class="submit" type="submit" value="Filter">');
    expect($output)->toContain('<input class="submit" type="button" value="Clear Filter"');
});

it('renders LedgerEntriesFormViewTemplate with correct form inputs', function () {
    ob_start();
    $this->formTemplate->render($this->formData);
    $output = ob_get_clean();

    expect($output)->toContain('<button class="submit" type="submit" name="save" value="save">Save</button>');
    expect($output)->toContain('<input type="hidden" name="id" value="0">');
    expect($output)->toContain('<input type="date" id="data_mov" name="data_mov" required value="2025-01-01">');
    expect($output)->toContain('<select name="categoryId">');
    expect($output)->toContain('<option value="10" >Sales</option>');
    expect($output)->toContain('<select name="currencyId">');
    expect($output)->toContain('<option value="1" >USD</option>');
    expect($output)->toContain('<select name="accountId">');
    expect($output)->toContain('<option value="1" >Cash</option>');
    expect($output)->toContain('<select name="direction">');
    expect($output)->toContain('<option value="D" >Debit</option>');
    expect($output)->toContain('<option value="C" >Credit</option>');
    expect($output)->toContain('<input type="number" step="0.01" name="currencyAmount" placeholder="0.00" value="100">');
    expect($output)->toContain('<input type="text" name="remarks" maxlength="255" value="Test">');
    expect($output)->toContain('1000'); // balance
});
