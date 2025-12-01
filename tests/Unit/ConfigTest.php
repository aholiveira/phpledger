<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\Config;
use Exception;

afterEach(function () {
    $ref = new \ReflectionClass(Config::class);
    $prop = $ref->getProperty('configData');
    $prop->setValue(null, []);
    $fileProp = $ref->getProperty('file');
    $fileProp->setValue(null, '');
});

it('initializes config from a valid JSON file', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode([
        'version' => 1,
        'title' => 'test',
        'storage' => ['type' => 'mysql', 'settings' => ['host' => 'localhost', 'user' => 'u', 'database' => 'db']],
        'smtp' => ['host' => 'smtp', 'from' => 'a@b.com'],
        'admin' => ['username' => 'admin', 'password' => 'pass'],
        'key1' => 'value1', // optional extra key
    ]));
    expect(Config::init($file, true))->toBeTrue();
    expect(Config::get('key1'))->toBe('value1');
    unlink($file);
});

it('fails initialization from a non-existing file', function () {
    expect(Config::init('/non/existing/file'))->toBeFalse();
});

it('sets and retrieves simple values', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    Config::init($file);
    Config::set('abc', 123, false);
    expect(Config::get('abc'))->toBe(123);
    unlink($file);
});

it('sets and retrieves nested values', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    Config::init($file);
    Config::set('nested.key', 'value', false);
    expect(Config::get('nested.key'))->toBe('value');
    unlink($file);
});

it('returns default for missing keys', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    Config::init($file);
    expect(Config::get('nonexistent', 'default'))->toBe('default');
    unlink($file);
});

it('overwrites existing keys', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode([
        'version' => 1,
        'title' => 'test',
        'storage' => ['type' => 'mysql', 'settings' => ['host' => 'localhost', 'user' => 'u', 'database' => 'db']],
        'smtp' => ['host' => 'smtp', 'from' => 'a@b.com'],
        'admin' => ['username' => 'admin', 'password' => 'pass'],
    ]));
    Config::init($file, true);
    Config::set('title', 'v1', false);
    Config::set('title', 'v2', false);
    expect(Config::get('title'))->toBe('v2');
    unlink($file);
});

it('validates correct config', function () {
    $valid = [
        'title' => 'App',
        'storage' => ['type' => 'mysql', 'settings' => ['host' => 'localhost', 'user' => 'u', 'database' => 'db']],
        'smtp' => ['host' => 'smtp', 'from' => 'a@b.com'],
        'admin' => ['username' => 'admin', 'password' => 'pass'],
        'version' => 2
    ];
    expect(Config::validate($valid, true))->toBeTrue();
});

it('invalidates config missing title', function () {
    $invalid = ['storage' => ['type' => 'file']];
    expect(Config::validate($invalid))->toBeFalse();
});

it('invalidates config missing storage type', function () {
    $invalid = ['title' => 'App', 'storage' => []];
    expect(Config::validate($invalid))->toBeFalse();
});

it('invalidates MySQL storage missing required fields', function () {
    $invalid = [
        'title' => 'App',
        'storage' => ['type' => 'mysql', 'settings' => ['host' => '', 'user' => '', 'database' => '']]
    ];
    expect(Config::validate($invalid))->toBeFalse();
});

it('saves and reloads config', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    Config::init($file);
    Config::set('version', 2, false);
    Config::set('title', 'Test', false);
    Config::set('storage.type', 'mysql', false);
    Config::set('storage.settings.host', 'localhost', false);
    Config::set('storage.settings.user', 'root', false);
    Config::set('storage.settings.database', 'contas', false);
    Config::set('smtp.host', 'smtp.example.com', false);
    Config::set('smtp.port', 25, false);
    Config::set('smtp.from', 'admin@example.com', false);
    Config::set('admin.username', 'admin', false);
    Config::set('admin.password', 'admin', false);
    Config::save();
    $data = json_decode(file_get_contents($file), true);
    expect($data['title'])->toBe('Test');
    expect($data['storage']['type'])->toBe('mysql');
    unlink($file);
});

it('throws when saving without a file', function () {
    Config::set('title', 'x');
    Config::set('storage.type', 'file');
    $ref = new \ReflectionClass(Config::class);
    $fileProp = $ref->getProperty('file');
    $fileProp->setValue(null, '');
    Config::save();
})->throws(Exception::class);

it('throws when saving to a non-writable location', function () {
    $dir = sys_get_temp_dir() . '/non_writable_dir_' . uniqid();
    mkdir($dir, 0555);
    $file = $dir . '/config.json';
    Config::init($file);
    Config::set('title', 'x');
    Config::set('storage.type', 'file');
    Config::save();
    chmod($dir, 0755);
    rmdir($dir);
})->throws(Exception::class);

it('throws when saving to a non-writable file', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(['title' => 'x', 'storage' => ['type' => 'file']]));
    chmod($file, 0444);
    Config::init($file);
    Config::set('title', 'y');
    Config::set('storage.type', 'file');
    Config::save();
    chmod($file, 0644);
    unlink($file);
})->throws(Exception::class);

it('returns false initializing with invalid JSON', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, '{invalid_json: }');
    expect(Config::init($file))->toBeFalse();
    unlink($file);
});
