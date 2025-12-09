<?php

namespace PHPLedgerTests\Unit\Views;

use PHPLedger\Views\Templates\LedgerEntryViewTemplate;

beforeEach(function () {
    $this->tpl = new LedgerEntryViewTemplate();
    $this->data = [
        'rowId' => 'r1',
        'lang' => 'en',
        'label' => [
            'id' => 'ID',
            'date' => 'Date',
            'category' => 'Category',
            'currency' => 'Currency',
            'account' => 'Account',
            'dc' => 'DC',
            'amount' => 'Amount',
            'remarks' => 'Remarks',
            'balance' => 'Balance',
        ],
        'title' => [
            'id' => 'View ID',
            'category' => 'View Category',
            'account' => 'View Account',
        ],
        'href' => [
            'id' => '/id/1',
            'category' => '/cat/2',
            'account' => '/acc/3',
        ],
        'text' => [
            'id' => '1',
            'date' => '2024-01-01',
            'category' => 'Food',
            'currency' => 'EUR',
            'account' => 'Bank',
            'dc' => 'D',
            'amount' => '50.00',
            'remarks' => 'Note',
            'balance' => '200.00',
        ],
    ];
});

it('renders the full ledger entry row correctly', function () {
    ob_start();
    $this->tpl->render($this->data);
    $html = trim(ob_get_clean());

    expect($html)->toContain('<tr id="r1">');
    expect($html)->toContain('<td lang="en" data-label="ID" class="id"><a title="View ID" href="/id/1">1</a></td>');
    expect($html)->toContain('<td lang="en" data-label="Date" class="data">2024-01-01</td>');
    expect($html)->toContain('<td lang="en" data-label="Category" class="category">');
    expect($html)->toContain('<a title="View Category" href="/cat/2">Food</a>');
    expect($html)->toContain('<td lang="en" data-label="Currency" class="currency">EUR</td>');
    expect($html)->toContain('<a title="View Account" href="/acc/3">Bank</a>');
    expect($html)->toContain('<td data-label="DC" class="direction">D</td>');
    expect($html)->toContain('<td data-label="Amount" class="amount">50.00</td>');
    expect($html)->toContain('<td data-label="Remarks" class="remarks">Note</td>');
    expect($html)->toContain('<td data-label="Balance" class="total">200.00</td>');
    expect($html)->toEndWith('</tr>');
});

it('throws no errors when minimal required data is passed', function () {
    $minimal = [
        'rowId' => 'x',
        'lang' => 'en',
        'label' => [
            'id' => '',
            'date' => '',
            'category' => '',
            'currency' => '',
            'account' => '',
            'dc' => '',
            'amount' => '',
            'remarks' => '',
            'balance' => '',
        ],
        'title' => ['id' => '', 'category' => '', 'account' => ''],
        'href' => ['id' => '#', 'category' => '#', 'account' => '#'],
        'text' => [
            'id' => '',
            'date' => '',
            'category' => '',
            'currency' => '',
            'account' => '',
            'dc' => '',
            'amount' => '',
            'remarks' => '',
            'balance' => '',
        ],
    ];

    ob_start();
    $this->tpl->render($minimal);
    ob_end_clean();

    expect(true)->toBeTrue();
});
