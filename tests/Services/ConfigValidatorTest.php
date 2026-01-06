<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Services\ConfigValidator;

function baseConfig(): array
{
    return [
        'version' => 1,
        'title' => 'App',
        'storage' => [
            'type' => 'mysql',
            'settings' => ['host' => 'localhost', 'user' => 'user', 'database' => 'db', 'port' => 3306],
        ],
        'smtp' => ['host' => 'smtp.example.com', 'from' => 'admin@example.com', 'port' => 25],
        'admin' => ['username' => 'admin', 'password' => 'secret'],
    ];
}

it('validates a correct configuration', function () {
    $cfg = new ConfigValidator(baseConfig());
    expect($cfg->validate())->toBeTrue();
    expect($cfg->getValidationMessage())->toBe('');
});

it('fails when version is missing', function () {
    $cfg = baseConfig();
    unset($cfg['version']);
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('version');
});

it('fails when title is empty', function () {
    $cfg = baseConfig();
    $cfg['title'] = '';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('title');
});

it('fails with unsupported storage type', function () {
    $cfg = baseConfig();
    $cfg['storage']['type'] = 'file';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('storage.type');
});

it('fails with missing storage.type', function () {
    $cfg = baseConfig();
    unset($cfg['storage']['type']);
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('storage.type');
});

it('fails with invalid storage.settings', function () {
    $cfg = baseConfig();
    $cfg['storage']['settings'] = [];
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('storage.settings');
});

it('fails when mysql host/user/database missing', function () {
    $cfg = baseConfig();
    $cfg['storage']['settings']['host'] = '';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('storage.settings.host');
});

it('fails when mysql port invalid', function () {
    $cfg = baseConfig();
    $cfg['storage']['settings']['port'] = 0;
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('storage.settings.port');
});

it('fails with missing SMTP host', function () {
    $cfg = baseConfig();
    $cfg['smtp']['host'] = '';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('host');
});

it('fails with invalid SMTP from email', function () {
    $cfg = baseConfig();
    $cfg['smtp']['from'] = 'invalid';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('from');
});

it('fails with invalid SMTP port', function () {
    $cfg = baseConfig();
    $cfg['smtp']['port'] = 0;
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('port');
});

it('fails when admin username/password missing', function () {
    $cfg = baseConfig();
    $cfg['admin']['username'] = '';
    $cfg['admin']['password'] = '';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();
    expect($validator->getValidationMessage())->toContain('admin.username');
});

it('handles multiple missing/invalid fields', function () {
    $cfg = baseConfig();
    unset($cfg['version']);           // first failing field
    $cfg['storage']['type'] = 'file';
    $cfg['admin']['username'] = '';
    $validator = new ConfigValidator($cfg);
    expect($validator->validate())->toBeFalse();

    // Only first failing field is reported
    $msg = $validator->getValidationMessage();
    expect($msg)->toContain('version');
});
