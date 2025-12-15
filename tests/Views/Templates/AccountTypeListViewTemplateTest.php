<?php

use PHPLedger\Views\Templates\AccountTypeListViewTemplate;
use PHPLedger\Contracts\UiBuilderInterface;

it('renders account type list HTML', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
        public function notification(array $notification, bool $success): void {}
    };

    $data = [
        'lang' => 'en',
        'ui' => $ui,
        'pagetitle' => 'Account Types',
        'label' => [
            'actions' => 'Actions',
            'id' => 'ID',
            'description' => 'Description',
            'savings' => 'Savings',
            'add' => 'Add',
            'edit' => 'Edit',
        ],
        'menu' => [],
        'footer' => [],
        'rows' => [
            ['id' => 1, 'description' => 'Type A', 'savings' => true],
            ['id' => 2, 'description' => 'Type B', 'savings' => false],
        ],
    ];

    ob_start();
    $template = new AccountTypeListViewTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<table class="lista account_type">');
    expect($html)->toContain('Type A');
    expect($html)->toContain('Type B');
    expect($html)->toContain('<td class="active" data-label="Savings">✓</td>');
    expect($html)->toContain('<td class="active" data-label="Savings">–</td>');
    expect($html)->toContain('Add');
    expect($html)->toContain('Edit');
    expect($html)->toContain('<tr id="1">');
    expect($html)->toContain('<tr id="2">');
});
