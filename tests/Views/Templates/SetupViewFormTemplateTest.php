<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Util\SetupState;
use PHPLedger\Views\Templates\SetupViewFormTemplate;

it('renders the config form correctly', function () {
    $data = [
        'state' => SetupState::CONFIG_REQUIRED,
        'saveLabel' => 'Save',
        'showCreateButton' => true,
        'label' => [
            'application_name' => 'App Name',
            'smtp_host' => 'SMTP Host',
            'smtp_port' => 'SMTP Port',
            'from' => 'From Email',
            'url' => 'URL',
            'storage_type' => 'Storage Type',
            'mysql_settings' => 'MySQL Settings',
            'host' => 'Host',
            'port' => 'Port',
            'database' => 'Database',
            'user' => 'User',
            'password' => 'Password',
            'save' => 'Save',
            'admin_username' => 'admin_username',
            'admin_password' => 'admin_password',
            'create_storage' => 'create_storage',
            'test_db' => 'test_db',
            'basic_configuration' => 'basic_configuration',
            'basic_configuration_help' => 'basic_configuration_help',
            'email_settings' => 'email_settings',
            'email_settings_help' => 'email_settings_help',
            'storage_settings' => 'storage_settings',
            'storage_settings_help' => 'storage_settings_help',
        ],
        'config' => [
            'title' => 'MyApp',
            'smtp' => [
                'host' => 'smtp.example.com',
                'port' => 587,
                'from' => 'noreply@example.com',
            ],
            'url' => 'http://example.com',
            'storage' => [
                'type' => 'mysql',
                'settings' => [
                    'host' => 'localhost',
                    'port' => 3306,
                    'database' => 'mydb',
                    'user' => 'root',
                    'password' => 'secret',
                ],
            ],
        ],
    ];

    ob_start();
    $template = new SetupViewFormTemplate();
    $template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<input id="title" name="title" value="MyApp" required>');
    expect($html)->toContain('<input id="smtp_host" name="smtp_host" value="smtp.example.com" placeholder="smtp.example.com" required>');
    expect($html)->toContain('<input id="smtp_port" type="number" min="1" max="65535" name="smtp_port" value="587" required>');
    expect($html)->toContain('<input id="smtp_from" name="smtp_from" value="noreply@example.com" required>');
    expect($html)->toContain('<input id="url" name="url" value="http://example.com" required>');
    expect($html)->toContain('<select id="storage_type" name="storage_type" required>');
    expect($html)->toContain('<option value="mysql" selected>MySQL</option>');
    expect($html)->toContain('<div class="settings mysql" id="mysql-settings" style="display: grid">');
    expect($html)->toContain('<input id="storage_host" name="storage_host"  value="localhost">');
    expect($html)->toContain('<input id="storage_port" name="storage_port" type="number" min="1" max="65535" value="3306">');
    expect($html)->toContain('<input id="storage_database" name="storage_database"  value="mydb">');
    expect($html)->toContain('<input id="storage_user" name="storage_user"  value="root">');
    expect($html)->toContain('<input id="storage_password" name="storage_password"  value="secret">');
    expect($html)->toContain('Save');
});
