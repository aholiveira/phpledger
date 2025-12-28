<?php

use PHPLedger\Contracts\UiBuilderInterface;
use PHPLedger\Util\SetupState;
use PHPLedger\Views\Templates\SetupViewFormTemplate;
use PHPLedger\Views\Templates\SetupViewTemplate;

it('renders config view template correctly', function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
        public function notification(array $notification, bool $success): void {}
    };

    $csrfToken = '<input type="hidden" name="csrf" value="token">';
    $data = [
        'lang' => 'en',
        'pagetitle' => 'Configuration',
        'appTitle' => 'AppTitle',
        'messages' => ['Saved successfully'],
        'success' => true,
        'label' => [
            'application_name' => 'App',
            'smtp_host' => 'SMTP Host',
            'smtp_port' => 'SMTP Port',
            'from' => 'From',
            'url' => 'URL',
            'storage_type' => 'Storage Type',
            'mysql_settings' => 'MySQL Settings',
            'save' => 'Save',
            'save_anyway' => 'Save anyway'
        ],
        'config' => [
            'title' => 'MyApp',
            'smtp' => ['host' => 'smtp.example.com', 'port' => 587, 'from' => 'noreply@example.com'],
            'url' => 'http://example.com',
            'storage' => ['type' => 'mysql', 'settings' => []],
        ],
        'csrf' => $csrfToken,
        'menu' => [],
        'footer' => [],
        'hasPermission' => true,
        'ui' => $ui,
        'state' => SetupState::COMPLETE,
        'setupViewFormTemplate' => new SetupViewFormTemplate,
    ];

    // Replace original template instance in render
    $template = new SetupViewTemplate();

    ob_start();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<div id="notification" class="notification success">');
    expect($html)->toContain('Saved successfully');
    expect($html)->toContain('<div class="maingrid">');
});
