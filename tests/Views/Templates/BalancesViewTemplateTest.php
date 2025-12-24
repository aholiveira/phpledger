<?php

use PHPLedger\Views\Templates\BalancesViewTemplate;
use PHPLedger\Util\UiBuilder;

uses()->group('balances-view');

beforeEach(function () {
    $this->view = new BalancesViewTemplate();

    $this->data = [
        'label' => [
            'account' => 'Account',
            'deposits' => 'Deposits',
            'withdrawals' => 'Withdrawals',
            'balance' => 'Balance',
            'percent' => 'Percent',
            'entries' => 'Entries',
            'edit_account' => 'Edit Account',
            'account_entries' => 'Account Entries',
            'list' => 'List'
        ],
        'action' => 'test',
        'menu' => [
            'accounts' => 'index.php?action=accounts'
        ],
        'footer' => [
            'repo' => 'https://github.com/aholiveira/phpledger',
            'versionText' => 'version:1.0',
            'sessionExpires' => 'expires:2030-01-01',
            'languageSelectorHtml' => '<a href="#">EN</a> | <span>PT</span>'
        ],
        'ui' => new UiBuilder(),
        'lang' => 'pt-pt',
        'pagetitle' => 'Balances',
        'appTitle' => 'AppTitle',
        'rows' => [
            [
                'text' => [
                    'name' => 'Cash',
                    'deposits' => '1000',
                    'withdrawals' => '200',
                    'balance' => '800',
                    'percent' => '80%'
                ],
                'href' => [
                    'name' => 'index.php?action=account&id=1',
                    'entries' => 'index.php?action=ledger_entries&account=1'
                ]
            ],
            [
                'text' => [
                    'name' => 'Bank',
                    'deposits' => '5000',
                    'withdrawals' => '1000',
                    'balance' => '4000',
                    'percent' => '80%',
                    'test' => 'actiontext'
                ],
                'href' => []
            ]
        ]
    ];
});

it('renders HTML with table and rows', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    expect($html)->toContain('<table class="lista saldos">')
        ->and($html)->toContain('Cash')
        ->and($html)->toContain('Bank')
        ->and($html)->toContain('1000')
        ->and($html)->toContain('4000')
        ->and($html)->toContain('<a title="Edit Account" href="index.php?action=account&id=1">Cash</a>')
        ->and($html)->toContain('<a title="Account Entries" href="index.php?action=ledger_entries&account=1">List</a>');
});

it('renders footer and menu', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    expect($html)->toContain('https://github.com/aholiveira/phpledger')
        ->and($html)->toContain('version:1.0')
        ->and($html)->toContain('<a href="#">EN</a> | <span>PT</span>')
        ->and($html)->toContain('index.php?action=accounts');
});

it('renders table headers from labels', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    foreach (['Account','Deposits','Withdrawals','Balance','Percent','Entries'] as $header) {
        expect($html)->toContain("<th>$header</th>");
    }
});

it('handles rows with missing href', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    // Bank row has no href
    expect($html)->toContain('<td class="account" data-label="Account">Bank</td>')
        ->not->toContain('index.php?action=account&id=2'); // should not generate link
});
