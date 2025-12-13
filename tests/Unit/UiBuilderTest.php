<?php

use PHPLedger\Util\UiBuilder;

beforeEach(function () {
    $this->ui = new UiBuilder();
});

it('renders the menu correctly', function () {
    $menuLinks = [
        'accounts' => 'index.php?action=accounts&lang=en',
        'ledger' => 'index.php?action=ledger&lang=en',
    ];
    $text = [
        'accounts' => 'Accounts',
        'ledger' => 'Ledger Entries',
    ];

    ob_start();
    $this->ui->menu($text, $menuLinks);
    $html = ob_get_clean();

    expect($html)->toContain('<aside class="menu">');
    expect($html)->toContain('<a id="accounts" aria-label="Accounts" href="index.php?action=accounts&lang=en">Accounts</a>');
    expect($html)->toContain('<a id="ledger" aria-label="Ledger Entries" href="index.php?action=ledger&lang=en">Ledger Entries</a>');
});

it('renders the footer correctly', function () {
    $footerData = [
        'repo' => 'https://github.com/aholiveira/phpledger',
        'versionText' => 'v1.0.0',
        'sessionExpires' => 'Session expires: 2025-12-31 23:59:59',
        'languageSelectorHtml' => '<a href="index.php?lang=en">EN</a> | <span>PT</span>',
    ];

    $text = []; // unused for now, but required parameter

    ob_start();
    $this->ui->footer($text, $footerData);
    $html = ob_get_clean();

    expect($html)->toContain('<footer>');
    expect($html)->toContain('<a href="https://github.com/aholiveira/phpledger"');
    expect($html)->toContain('v1.0.0');
    expect($html)->toContain('Session expires: 2025-12-31 23:59:59');
    expect($html)->toContain('<a href="index.php?lang=en">EN</a> | <span>PT</span>');
});
