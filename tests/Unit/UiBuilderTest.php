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

it('renders the menu with missing text gracefully', function () {
    $menuLinks = [
        'accounts' => 'index.php?action=accounts&lang=en',
    ];
    $text = []; // no labels

    ob_start();
    $this->ui->menu($text, $menuLinks);
    $html = ob_get_clean();

    expect($html)->toContain('<a id="accounts" aria-label="" href="index.php?action=accounts&lang=en"></a>');
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

it('renders the footer with missing optional fields', function () {
    $footerData = []; // all missing

    ob_start();
    $this->ui->footer([], $footerData);
    $html = ob_get_clean();

    expect($html)->toContain('<footer>');
    expect($html)->toContain('<a href="" aria-label=""></a>');
});

it('renders a success notification', function () {
    ob_start();
    $this->ui->notification('Success message', true);
    $html = ob_get_clean();

    expect($html)->toContain('<div id="notification" class="notification success">');
    expect($html)->toContain('Success message');
    expect($html)->toContain('setTimeout');
});

it('renders a failure notification', function () {
    ob_start();
    $this->ui->notification('Error occurred', false);
    $html = ob_get_clean();

    expect($html)->toContain('<div id="notification" class="notification fail">');
    expect($html)->toContain('Error occurred');
});

it('does not render notification if message is empty', function () {
    ob_start();
    $this->ui->notification('', true);
    $html = ob_get_clean();

    expect($html)->toBe('');
});
