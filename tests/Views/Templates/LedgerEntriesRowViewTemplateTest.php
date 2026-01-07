<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\LedgerEntriesRowViewTemplate;

beforeEach(function () {
    $this->rowTemplate = new LedgerEntriesRowViewTemplate();

    $this->data = [
        'lang' => 'en',
        'text' => [
            'id' => 123,
            'date' => '2025-12-16',
            'category' => 'Sales',
            'currency' => 'USD',
            'account' => 'Cash',
            'direction' => 'D',
            'amount' => '100.00',
            'exchangeRate' => '1.00',
            'euroAmount' => '1.00',
            'remarks' => 'Test Entry',
            'balance' => '1000.00'
        ],
        'title' => [
            'editlink' => 'Edit Entry',
            'category' => 'Category Link',
            'account' => 'Account Link'
        ],
        'href' => [
            'editlink' => '/edit/123',
            'category' => '/category/10',
            'account' => '/account/1'
        ],
        'label' => [
            'edit' => 'Edit',
            'id' => 'ID',
            'date' => 'Date',
            'category' => 'Category',
            'currency' => 'Currency',
            'account' => 'Account',
            'dc' => 'DC',
            'amount' => 'Amount',
            'remarks' => 'Remarks',
            'balance' => 'Balance',
            'exchangeRate' => 'exchangeRate',
            'euro' => 'euro',
        ]
    ];
});

it('renders a table row with correct cells and links', function () {
    ob_start();
    $this->rowTemplate->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<tr id="123">');
    expect($output)->toContain('<td lang="en" data-label="ID" class="id">123</td>');
    expect($output)->toContain('<td lang="en" data-label="Date" class="data">2025-12-16</td>');
    expect($output)->toContain('<td lang="en" data-label="Category" class="category">');
    expect($output)->toContain('<td lang="en" data-label="Currency" class="currency">USD</td>');
    expect($output)->toContain('<a title="Category Link" href="/category/10">Sales</a>');
    expect($output)->toContain('<td lang="en" data-label="Account" class="account">');
    expect($output)->toContain('<a title="Account Link" href="/account/1">Cash</a>');
    expect($output)->toContain('<td data-label="DC" class="direction">D</td>');
    expect($output)->toContain('<td data-label="Amount" class="amount">100.00</td>');
    expect($output)->toContain('<td data-label="Remarks" class="remarks">Test Entry</td>');
    expect($output)->toContain('<td data-label="Balance" class="total">1000.00</td>');
    expect($output)->toContain('<td lang="en" data-label="Edit" class="id"><a title="Edit Entry" href="/edit/123">Edit</a></td>');
});
