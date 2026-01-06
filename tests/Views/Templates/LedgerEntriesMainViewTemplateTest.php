<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\LedgerEntriesMainViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesFilterViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesTableViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesRowViewTemplate;
use PHPLedger\Views\Templates\LedgerEntriesFormViewTemplate;

beforeEach(function () {
    $this->mainTemplate = new LedgerEntriesMainViewTemplate();

    // Use real instances for final templates
    $this->filterViewTemplate = new LedgerEntriesFilterViewTemplate();
    $this->tableViewTemplate  = new LedgerEntriesTableViewTemplate();
    $this->rowViewTemplate    = new LedgerEntriesRowViewTemplate();
    $this->formViewTemplate   = new LedgerEntriesFormViewTemplate();

    // Minimal data for rendering
    $this->data = [
        'ui' => new class {
            public function menu($label, $menu)
            {
                echo '<!-- menu called -->';
            }
            public function footer($label, $footer)
            {
                echo '<!-- footer called -->';
            }
        },
        'lang' => 'en',
        'label' => [
            'actions' => 'Actions',
            'start' => 'Start',
            'end' => 'End',
            'account' => 'Account',
            'category' => 'Category',
            'no_filter' => 'No Filter',
            'filter' => 'Filter',
            'clear_filter' => 'Clear Filter',
            'id' => 'id',
            'date' => 'date',
            'currency' => 'currency',
            'dc' => 'dc',
            'amount' => 'amount',
            'remarks' => 'remarks',
            'balance' => 'balance',
            'previous_balance' => 'previous_balance',
            'save' => 'save',
            'download_data' => 'download_data'
        ],
        'menu' => [],
        'filterFormData' => [
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
            'accounts' => [],
            'entryCategory' => []
        ],
        'filters' => [],
        'isEditing' => false,
        'editId' => 0,
        'formData' => [
            'id' => 0,
            'date' => '2025-01-01',
            'entryCategoryRows' => [],
            'currencyRows' => [],
            'accountRows' => [],
            'direction' => [],
            'amount' => 0,
            'remarks' => '',
            'balance' => 0
        ],
        'startBalance' => 0,
        'ledgerEntryRows' => [],
        'csrf' => '<input type="hidden" name="csrf_token" value="123">',
        'rowViewTemplate' => $this->rowViewTemplate,
        'formViewTemplate' => $this->formViewTemplate,
        'transactionsInPeriod' => 'Transactions: 0',
        'footer' => [],
        'filterViewTemplate' => $this->filterViewTemplate,
        'tableViewTemplate' => $this->tableViewTemplate,
        'downloadUrl' => ''
    ];
});

it('renders main template with sub-templates', function () {
    ob_start();
    $this->mainTemplate->render($this->data);
    $output = ob_get_clean();

    // Assertions
    expect($output)->toContain('<!-- menu called -->');
    expect($output)->toContain('Transactions: 0');
    expect($output)->toContain('<form'); // Filter form should exist
    expect($output)->toContain('<table'); // Table should exist
});
