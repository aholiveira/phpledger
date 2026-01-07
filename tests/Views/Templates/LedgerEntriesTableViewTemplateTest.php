<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\LedgerEntriesTableViewTemplate;

beforeEach(function () {
    $this->tableTemplate = new LedgerEntriesTableViewTemplate();

    $this->rowViewTemplate = Mockery::mock();
    $this->rowViewTemplate->shouldReceive('render')->andReturnNull();

    $this->formViewTemplate = Mockery::mock();
    $this->formViewTemplate->shouldReceive('render')->andReturnNull();

    $this->data = [
        'lang' => 'en',
        'label' => [
            'actions' => 'Actions',
            'id' => 'ID',
            'date' => 'Date',
            'category' => 'Category',
            'currency' => 'Currency',
            'account' => 'Account',
            'dc' => 'DC',
            'amount' => 'Amount',
            'remarks' => 'Remarks',
            'balance' => 'Balance',
            'previous_balance' => 'Previous Balance',
            'download_data' => 'download_data',
            'exchangeRate' => 'exchangeRate',
            'euro' => 'euro',
        ],
        'csrf' => '<input type="hidden" name="csrf_token" value="123">',
        'ledgerEntryRows' => [
            [
                'text' => [
                    'id' => 1,
                    'date' => '2025-12-16',
                    'category' => 'Sales',
                    'currency' => 'USD',
                    'account' => 'Cash',
                    'direction' => 'D',
                    'amount' => '100.00',
                    'remarks' => 'Test',
                    'balance' => '1000.00'
                ],
                'title' => ['editlink' => 'Edit', 'category' => 'Category', 'account' => 'Account'],
                'href' => ['editlink' => '/edit/1', 'category' => '/cat/1', 'account' => '/acc/1']
            ],
            [
                'text' => [
                    'id' => 2,
                    'date' => '2025-12-16',
                    'category' => 'Sales',
                    'currency' => 'USD',
                    'account' => 'Cash',
                    'direction' => 'D',
                    'amount' => '100.00',
                    'remarks' => 'Test',
                    'balance' => '1000.00'
                ],
                'title' => ['editlink' => 'Edit', 'category' => 'Category', 'account' => 'Account'],
                'href' => ['editlink' => '/edit/1', 'category' => '/cat/1', 'account' => '/acc/1']
            ]
        ],
        'formData' => [
            'id' => 0,
            'date' => '2025-12-16',
            'entryCategoryRows' => [],
            'currencyRows' => [],
            'accountRows' => [],
            'direction' => [],
            'amount' => 0,
            'remarks' => '',
            'balance' => 1000
        ],
        'startBalance' => 500,
        'editId' => 1,
        'filters' => []
    ];
});

it('renders table wrapper and previous balance', function () {
    ob_start();
    $this->tableTemplate->render(array_merge($this->data, [
        'rowViewTemplate' => $this->rowViewTemplate,
        'formViewTemplate' => $this->formViewTemplate
    ]));
    $output = ob_get_clean();

    expect($output)->toContain('<div class="table-wrapper">');
    expect($output)->toContain('<td class="balance-label" colspan="11">Previous Balance</td>');
    expect($output)->toContain('<td data-label="Previous Balance" class="balance">');
});
