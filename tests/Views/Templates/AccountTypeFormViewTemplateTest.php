<?php

use PHPLedger\Views\Templates\AccountTypeFormViewTemplate;
use PHPLedger\Contracts\UiBuilderInterface;

it('renders account type form HTML', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
        public function notification(array $notification, bool $success): void {}
    };

    $data = [
        'lang' => 'en',
        'ui' => $ui,
        'appTitle' => 'AppTitle',
        'label' => [
            'back_to_list' => 'Back to list',
            'id' => 'ID',
            'description' => 'Description',
            'savings' => 'Savings',
            'save' => 'Save',
            'delete' => 'Delete',
            'are_you_sure_you_want_to_delete' => 'Are you sure?',
        ],
        'menu' => [],
        'footer' => [],
        'notification' => [],
        'success' => true,
        'csrf' => '<input type="hidden" name="csrf" value="token">',
        'row' => [
            'id' => 1,
            'description' => 'Test Type',
            'savings' => true,
        ],
    ];

    ob_start();
    $template = new AccountTypeFormViewTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<form method="POST" action="index.php?action=account_type&id=1">');
    expect($html)->toContain('Back to list');
    expect($html)->toContain('Description');
    expect($html)->toContain('Savings');
    expect($html)->toContain('Save');
    expect($html)->toContain('Delete');
    expect($html)->toContain('<input type="hidden" name="csrf" value="token">');
    expect($html)->toContain('Test Type');
});
