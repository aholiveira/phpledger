<?php

use PHPLedger\Views\Templates\AccountFormViewTemplate;
use PHPLedger\Contracts\UiBuilderInterface;

it('renders account form HTML', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
    };

    $data = [
        'lang' => 'en',
        'pagetitle' => 'Test Page',
        'ui' => $ui,
        'csrf' => '',
        'label' => [
            'id' => 'ID',
            'name' => 'Name',
            'number' => 'Number',
            'type' => 'Type',
            'iban' => 'IBAN',
            'swift' => 'SWIFT',
            'openDate' => 'Open Date',
            'closeDate' => 'Close Date',
            'active' => 'Active',
            'save' => 'Save',
            'delete' => 'Delete',
            'back_to_list' => 'Back to list',
            'back_to_balances' => 'Back to balances',
            'check_your_data' => 'Check your data',
            'name_required' => 'Name required',
        ],
        'footer' => [],
        'back' => 'accounts',
        'text' => [
            'id' => 1,
            'name' => 'My Account',
            'number' => '12345',
            'typeId' => 2,
            'iban' => 'IBAN123',
            'swift' => 'SWIFT123',
            'openDate' => '2025-01-01',
            'closeDate' => '2025-12-31',
            'active' => true,
        ],
        'menu' => [],
        'errors' => [],
        'accountTypes' => [
            ['id' => 1, 'text' => 'Checking', 'parentId' => 0, 'value' => 1, 'selected' => false],
            ['id' => 2, 'text' => 'Savings', 'parentId' => 0, 'value' => 2, 'selected' => true],
        ],
    ];

    ob_start();
    $template = new AccountFormViewTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<form method="POST" action="index.php?action=account&id=1">');
    expect($html)->toContain('Name');
    expect($html)->toContain('Number');
    expect($html)->toContain('IBAN');
    expect($html)->toContain('SWIFT');
    expect($html)->toContain('Open Date');
    expect($html)->toContain('Close Date');
    expect($html)->toContain('Active');
    expect($html)->toContain('Save');
    expect($html)->toContain('Delete');
    expect($html)->toContain('Back to list');
});

it('renders account form HTML with back action to balances', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
    };

    $data = [
        'lang' => 'en',
        'pagetitle' => 'Test Page',
        'ui' => $ui,
        'csrf' => '',
        'label' => [
            'id' => 'ID',
            'name' => 'Name',
            'number' => 'Number',
            'type' => 'Type',
            'iban' => 'IBAN',
            'swift' => 'SWIFT',
            'openDate' => 'Open Date',
            'closeDate' => 'Close Date',
            'active' => 'Active',
            'save' => 'Save',
            'delete' => 'Delete',
            'back_to_list' => 'Back to list',
            'back_to_balances' => 'Back to balances',
            'check_your_data' => 'Check your data',
            'name_required' => 'Name required',
        ],
        'footer' => [],
        'back' => 'balances',
        'text' => [
            'id' => 1,
            'name' => 'My Account',
            'number' => '12345',
            'typeId' => 2,
            'iban' => 'IBAN123',
            'swift' => 'SWIFT123',
            'openDate' => '2025-01-01',
            'closeDate' => '2025-12-31',
            'active' => true,
        ],
        'menu' => [],
        'errors' => [],
        'accountTypes' => [
            ['id' => 1, 'text' => 'Checking', 'parentId' => 0, 'value' => 1, 'selected' => false],
            ['id' => 2, 'text' => 'Savings', 'parentId' => 0, 'value' => 2, 'selected' => true],
        ],
    ];

    ob_start();
    $template = new AccountFormViewTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<a href="index.php?action=balances');
    expect($html)->toContain('Back to balances');
    expect($html)->toContain('Name');
    expect($html)->toContain('Number');
    expect($html)->toContain('IBAN');
    expect($html)->toContain('SWIFT');
    expect($html)->toContain('Open Date');
    expect($html)->toContain('Close Date');
    expect($html)->toContain('Active');
    expect($html)->toContain('Save');
    expect($html)->toContain('Delete');
});

it('renders name errors', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
    };

    $data = [
        'lang' => 'en',
        'pagetitle' => 'Test Page',
        'ui' => $ui,
        'csrf' => '',
        'label' => [
            'id' => 'ID',
            'name' => 'Name',
            'number' => 'Number',
            'type' => 'Type',
            'iban' => 'IBAN',
            'swift' => 'SWIFT',
            'openDate' => 'Open Date',
            'closeDate' => 'Close Date',
            'active' => 'Active',
            'save' => 'Save',
            'delete' => 'Delete',
            'back_to_list' => 'Back to list',
            'back_to_balances' => 'Back to balances',
            'check_your_data' => 'Check your data',
            'name_required' => 'Name required',
        ],
        'footer' => [],
        'back' => 'balances',
        'text' => [
            'id' => 1,
            'name' => 'My Account',
            'number' => '12345',
            'typeId' => 2,
            'iban' => 'IBAN123',
            'swift' => 'SWIFT123',
            'openDate' => '2025-01-01',
            'closeDate' => '2025-12-31',
            'active' => true,
        ],
        'menu' => [],
        'errors' => ['name', 'other'],
        'accountTypes' => [
            ['id' => 1, 'text' => 'Checking', 'parentId' => 0, 'value' => 1, 'selected' => false],
            ['id' => 2, 'text' => 'Savings', 'parentId' => 0, 'value' => 2, 'selected' => true],
        ],
    ];

    ob_start();
    $template = new AccountFormViewTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<p style="color:red">Name required</p>');
    expect($html)->toContain('<p style="color:red">Check your data</p>');
});
