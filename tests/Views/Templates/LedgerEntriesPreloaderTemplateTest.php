<?php

use PHPLedger\Views\Templates\LedgerEntriesPreloaderTemplate;
use PHPLedger\Util\Html;

beforeEach(function () {
    $this->preloaderTemplate = new LedgerEntriesPreloaderTemplate();

    $this->data = [
        'lang' => 'en',
        'pagetitle' => 'Ledger Entries',
        'appTitle' => 'AppTitle',
        'label' => ['notification' => 'Loading...'],
        'success' => true,
        'ui' => new class {
            public function notification($message, $success) {
                echo '<div class="notification">' . htmlspecialchars($message) . "</div>";
            }
        }
    ];
});

it('renders the preloader HTML correctly', function () {
    ob_start();
    $this->preloaderTemplate->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<!DOCTYPE html>');
    expect($output)->toContain('<html lang="en"');
    expect($output)->toMatch('/<title>Ledger Entries - .*<\/title>/');
    expect($output)->toContain('<script src="assets/js/ledger_entries.js">');
    expect($output)->toContain('<div class="notification">Loading...</div>');
    expect($output)->toContain('<div id="preloader">');
    expect($output)->toContain('<div class="spinner"></div>');
    expect($output)->toContain('<div class="maingrid">');
});
