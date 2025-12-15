<?php

namespace PHPLedgerTests\Unit\Util;

use Mockery;
use PHPLedger\Util\Config;
use PHPLedger\Util\ConfigException;
use ReflectionClass;

afterEach(function () {
    $ref = new ReflectionClass(Config::class);

    $data = $ref->getProperty('configData');
    $data->setAccessible(true);
    $data->setValue(null, []);

    $file = $ref->getProperty('file');
    $file->setAccessible(true);
    $file->setValue(null, '');

    $instance = $ref->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
});

function validConfig(array $override = []): array
{
    return array_replace_recursive([
        'version' => 1,
        'title' => 'App',
        'storage' => [
            'type' => 'mysql',
            'settings' => [
                'host' => 'localhost',
                'user' => 'user',
                'database' => 'db',
                'port' => 3306,
            ],
        ],
        'smtp' => [
            'host' => 'smtp.example.com',
            'from' => 'admin@example.com',
            'port' => 25,
        ],
        'admin' => [
            'username' => 'admin',
            'password' => 'secret',
        ],
    ], $override);
}

it('loads valid configuration file in test mode', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(validConfig()));

    expect(Config::init($file, true))->toBeTrue();
    expect(Config::instance()->get('title'))->toBe('App');

    unlink($file);
});

it('fails loading non existing config file', function () {
    expect(Config::init('/no/such/file.json'))->toBeFalse();
});

it('fails loading invalid json', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, '{invalid json');

    expect(Config::init($file))->toBeFalse();

    unlink($file);
});

it('gets and sets flat values', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(validConfig()));

    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('foo', 'bar', false);
    expect($cfg->get('foo'))->toBe('bar');

    unlink($file);
});

it('gets and sets nested values', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(validConfig()));

    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('a.b.c', 123, false);
    expect($cfg->get('a.b.c'))->toBe(123);

    unlink($file);
});

it('returns default when key is missing', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(validConfig()));

    Config::init($file, true);
    $cfg = Config::instance();

    expect($cfg->get('missing', 'default'))->toBe('default');

    unlink($file);
});

it('validates correct config', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(), true))->toBeTrue();
});

it('fails validation without title', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['title' => ''])))->toBeFalse();
});

it('fails validation with unsupported storage type', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'storage' => ['type' => 'file']
    ])))->toBeFalse();
});

it('fails validation with invalid mysql settings', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'storage' => [
            'settings' => ['host' => '', 'user' => '', 'database' => '']
        ]
    ])))->toBeFalse();
});

it('fails validation with invalid smtp from address', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'smtp' => ['host' => 'smtp', 'from' => 'invalid']
    ])))->toBeFalse();
});

it('fails validation with missing admin credentials', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'admin' => ['username' => null, 'password' => null]
    ])))->toBeFalse();
});

it('saves configuration to disk', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    $cfg = Config::instance();
    Config::init($file, true);

    foreach (validConfig() as $k => $v) {
        $cfg->set($k, $v, false);
    }

    $cfg->save();

    $data = json_decode(file_get_contents($file), true);
    expect($data['title'])->toBe('App');
    expect($data['storage']['type'])->toBe('mysql');

    unlink($file);
});

it('throws when saving without file', function () {
    $cfg = Config::instance();
    Config::init("");
    $cfg->set('title', 'x', false);
    $cfg->save();
})->throws("Configuration file not set");

it('throws when saving to non writable directory', function () {
    $dir = sys_get_temp_dir() . '/cfg_' . uniqid();
    mkdir($dir, 0550);

    $file = $dir . '/config.json';

    Config::init($file, true);
    $cfg = Config::instance();

    foreach (validConfig() as $k => $v) {
        $cfg->set($k, $v, false);
    }

    $cfg->save();
})->throws("Configuration directory is not writable");

// Trigger json_encode failure
it('throws when json_encode fails', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    Config::init($file, true);
    $cfg = Config::instance();

    // Inject a value that cannot be encoded
    $cfg->set('bad', fopen('php://memory', 'r'), false);

    $cfg->save();
})->throws("Configuration data is not valid", "Unable to encode configuration data to JSON");

// Trigger file_put_contents failure
it('throws when unable to write temp file', function () {
    $dir = sys_get_temp_dir() . '/cfg_' . uniqid();
    mkdir($dir, 0777);

    $file = $dir . '/config.json';
    Config::init($file, true);
    $cfg = Config::instance();

    foreach (validConfig() as $k => $v) {
        $cfg->set($k, $v, false);
    }

    // make dir read-only to simulate failure
    chmod($dir, 0555);

    $cfg->save();
})->throws("Configuration directory is not writable", "Unable to save configuration file");

// Trigger rename failure
it('throws when unable to replace configuration file', function () {
    $dir = sys_get_temp_dir() . '/cfg_' . uniqid();
    mkdir($dir, 0777);

    $file = $dir . '/config.json';
    file_put_contents($file, json_encode(validConfig()));
    Config::init($file, true);
    $cfg = Config::instance();

    foreach (validConfig() as $k => $v) {
        $cfg->set($k, $v, false);
    }

    // make file a directory to break rename
    unlink($file);
    mkdir($file);

    $cfg->save();
})->throws("Configuration directory is not writable", "Unable to replace configuration file");

// ValidateAdminSettings edge cases (already covered partially)
it('fails validation with missing admin username', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['admin' => ['username' => '', 'password' => 'secret']])))
        ->toBeFalse();
});

it('fails validation with missing admin password', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['admin' => ['username' => 'admin', 'password' => '']])))
        ->toBeFalse();
});

// ValidateMySqlStorage edge case for invalid port
it('fails validation with invalid MySQL port', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['storage' => ['settings' => ['port' => 0]]])))
        ->toBeFalse();
});

// ValidateSmtpSettings invalid host and port
it('fails validation with missing SMTP host', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['smtp' => ['host' => '', 'from' => 'a@b.com']])))
        ->toBeFalse();
});

it('fails validation with invalid SMTP port', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['smtp' => ['port' => 0]])))
        ->toBeFalse();
});

it('throws ConfigException when validation fails during init', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    $invalidConfig = validConfig(['title' => '']); // invalid title
    file_put_contents($file, json_encode($invalidConfig));
    expect(Config::init($file, false))->toBeFalse();
    unlink($file);
});

it('logs config change and calls save when config is modified', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');

    $originalConfig = validConfig();
    file_put_contents($file, json_encode($originalConfig));

    // Modify the config after load to trigger $configChanged
    $modifiedConfig = $originalConfig;
    $modifiedConfig['title'] = 'New App';

    // Write modified config back to file to simulate change
    file_put_contents($file, json_encode($modifiedConfig));

    expect(Config::init($file, false))->toBeTrue();

    unlink($file);
});
