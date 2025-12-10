<?php

use PHPLedger\Views\Templates\BalancesViewTemplate;
use PHPLedger\Util\L10n;
use PHPLedgerTests\Unit\Util\DummyApp;

uses()->group('template');

beforeEach(function () {
    $this->template = new BalancesViewTemplate();
    $this->app = new DummyApp;
});

test('renders minimal page structure', function () {
    $data = [
        'lang' => 'en',
        'title' => 'Balances',
        'app' => $this->app,
        'isAdmin' => true,
        'action' => 'index',
        'l10n' => [
            'account' => "Account",
            'deposits' => "Deposits",
            'withdrawals' => "Withdrawals",
            'balance' => "Balance",
            'percent' => "Percent",
            'entries' => "Entries",
            'edit_account' => "Edit account",
            'account_entries' => "Account entries",
            'list' => "List"
        ],
        'rows' => [],
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<!DOCTYPE html>');
    expect($html)->toContain('<html lang="en">');
    expect($html)->toContain('<table class="lista saldos">');
});

test('renders a row without links', function () {
    $data = [
        'lang' => 'en',
        'title' => 'Balances',
        'app' => $this->app,
        'isAdmin' => false,
        'action' => 'index',
        'l10n' => [
            'account' => "Account",
            'deposits' => "Deposits",
            'withdrawals' => "Withdrawals",
            'balance' => "Balance",
            'percent' => "Percent",
            'entries' => "Entries",
            'edit_account' => "Edit account",
            'account_entries' => "Account entries",
            'list' => "List"
        ],
        'rows' => [
            [
                'text' => [
                    'name' => 'Main Account',
                    'deposits' => '10',
                    'withdrawals' => '2',
                    'balance' => '8',
                    'percent' => '80%',
                ],
                'href' => [
                    'name' => '',
                    'entries' => '',
                ]
            ]
        ],
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<td class="account" data-label="Account">');
    expect($html)->toContain('Main Account');
    expect($html)->not->toContain('<a title="Edit account"');
    expect($html)->toContain('<td class="percent" data-label="Percent">80%</td>');
});

test('renders a row with links enabled', function () {
    $data = [
        'lang' => 'en',
        'title' => 'Balances',
        'app' => $this->app,
        'isAdmin' => true,
        'action' => 'index',
        'l10n' => [
            'account' => "Account",
            'deposits' => "Deposits",
            'withdrawals' => "Withdrawals",
            'balance' => "Balance",
            'percent' => "Percent",
            'entries' => "Entries",
            'edit_account' => "Edit account",
            'account_entries' => "Account entries",
            'list' => "List"
        ],
        'rows' => [
            [
                'text' => [
                    'name' => 'Test Account',
                    'deposits' => '100',
                    'withdrawals' => '50',
                    'balance' => '50',
                    'percent' => '50%',
                ],
                'href' => [
                    'name' => '/edit/1',
                    'entries' => '/entries/1'
                ]
            ]
        ],
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<a title="Edit account" href="/edit/1">');
    expect($html)->toContain('<a title="Account entries" href="/entries/1">List</a>');
    expect($html)->toContain('Test Account');
});
