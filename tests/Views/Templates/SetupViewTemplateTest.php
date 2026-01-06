<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

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
            'save_anyway' => 'Save anyway',
            'storage_does_not_exist' => 'Storage does not exist',
            'pending_db_migrations_detected' => 'pending_db_migrations_detected',
            'no_admin_user_detected' => 'no_admin_user_detected',
            'create_admin_user' => 'create_admin_user',
            'setup_complete' => 'setup_complete',
            'login_page' => 'login_page',
            'admin_username' => 'admin_username',
            'admin_password' => 'admin_password',
            'create_storage' => 'create_storage',
            'run_migrations' => 'run_migrations',
            'apply_migrations' => 'apply_migrations',
            'basic_configuration' => 'basic_configuration',
            'basic_configuration_help' => 'basic_configuration_help',
            'email_settings' => 'email_settings',
            'email_settings_help' => 'email_settings_help',
            'storage_settings' => 'storage_settings',
            'storage_settings_help' => 'storage_settings_help',
            'host' => 'host',
            'port' => 'port',
            'database' => 'database',
            'user' => 'user',
            'password' => 'password',
            'test_db' => 'test_db',
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

    expect($html)->toContain('<div id="notification" role="alert" aria-live="assertive" class="notification success">');
    expect($html)->toContain('Saved successfully');
    expect($html)->toContain('<div class="config">');
});
