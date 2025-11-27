<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\Config;

afterEach(function () {
    $ref = new \ReflectionClass(Config::class);
    $prop = $ref->getProperty('configData');
    $prop->setValue(null, []);
});

it('initializes config from a valid json file', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode(['key1' => 'value1']));
    expect(Config::init($file))->toBeTrue();
    expect(Config::get('key1'))->toBe('value1');
    unlink($file);
});

it('fails initialization from a non-existing file', function () {
    expect(Config::init('/non/existing/file'))->toBeFalse();
});

it('sets and retrieves values', function () {
    Config::set('abc', 123);
    expect(Config::get('abc'))->toBe(123);
});

it('returns null for missing keys', function () {
    expect(Config::get('nonexistent'))->toBeNull();
});

it('overwrites existing keys', function () {
    Config::set('k', 'v1');
    Config::set('k', 'v2');
    expect(Config::get('k'))->toBe('v2');
});

it('does not break when configData is corrupted', function () {
    $ref = new \ReflectionClass(Config::class);
    $prop = $ref->getProperty('configData');
    $prop->setValue(null, null);
    Config::set('test', 'ok');
    expect(Config::get('test'))->toBe('ok');
});
