<?php

use PHPLedger\Util\UiBuilder;
use PHPLedger\Views\Templates\AccountListViewTemplate;

beforeEach(function () {
    $this->view = new AccountListViewTemplate();
    $this->ui = new UiBuilder();
    $this->data = [
        'list' => [
            [
                'id' => 1,
                'name' => 'Cash',
                'number' => '101',
                'type' => 'Asset',
                'iban' => 'PT50000201231234567890154',
                'swift' => 'BCPTPTPL',
                'openDate' => '2025-01-01',
                'closeDate' => '',
                'activa' => true,
            ],
            [
                'id' => 2,
                'name' => 'Bank',
                'number' => '102',
                'type' => 'Asset',
                'iban' => 'PT50000201231234567890155',
                'swift' => 'BCPTPTPL',
                'openDate' => '2025-01-01',
                'closeDate' => '2025-12-31',
                'activa' => false,
            ],
        ],
        'label' => [
            'add' => 'Add',
            'id' => 'ID',
            'name' => 'Name',
            'number' => 'Number',
            'type' => 'Type',
            'iban' => 'IBAN',
            'swift' => 'SWIFT',
            'open' => 'Open',
            'close' => 'Close',
            'active' => 'Active',
            'actions' => 'Actions',
            'yes' => 'Yes',
            'no' => 'No',
            'edit' => 'Edit',
        ],
        'menu' => [],
        'footer' => ['repo' => ''],
        'ui' => $this->ui,
        'lang' => 'en',
        'pagetitle' => 'Accounts',
    ];
});

it('renders the account list correctly', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    expect($html)->toContain('<table class="lista entry_category">');
    expect($html)->toContain('<th>ID</th>');
    expect($html)->toContain('<td data-label="ID">1</td>');
    expect($html)->toContain('<td class="active" data-label="Active">✓</td>');
    expect($html)->toContain('<td class="active" data-label="Active">–</td>');
    expect($html)->toContain('<a href="index.php?action=account&id=1&lang=en" aria-label="Edit">Edit</a>');
    expect($html)->toContain('<a href="index.php?action=account&id=2&lang=en" aria-label="Edit">Edit</a>');
});

it('renders the preloader script', function () {
    ob_start();
    $this->view->render($this->data);
    $html = ob_get_clean();

    expect($html)->toContain("document.getElementById('preloader').style.display = 'none';");
});
