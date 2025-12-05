<?php

namespace PHPLedgerTests\Util;

use PHPLedger\Util\ConfigPath;
use PHPLedger\Util\Path;

beforeEach(function () {
    $this->root = sys_get_temp_dir() . '/phpledger_test_' . uniqid();
    mkdir($this->root, 0777, true);
    ConfigPath::setBaseDir($this->root);
});

afterEach(function () {
    if (is_dir($this->root)) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }
});

it('returns correct path', function () {
    $expected = Path::combine($this->root, 'config', 'config.json');
    expect(ConfigPath::get())->toBe($expected);
});

it('does nothing if new file exists', function () {
    $newPath = ConfigPath::get();
    mkdir(dirname($newPath), 0777, true);
    file_put_contents($newPath, 'x');

    ConfigPath::ensureMigrated();

    expect(file_get_contents($newPath))->toBe('x');
});

it('does nothing if old file does not exist', function () {
    ConfigPath::ensureMigrated();

    $newPath = ConfigPath::get();
    expect(file_exists($newPath))->toBeFalse();
});

it('migrates old file to new path', function () {
    $old = Path::combine($this->root, 'config.json');
    file_put_contents($old, 'old content');

    ConfigPath::ensureMigrated();

    $newPath = ConfigPath::get();
    expect(file_exists($old))->toBeFalse();
    expect(file_get_contents($newPath))->toBe('old content');
});

it('creates directory if missing when migrating', function () {
    $old = Path::combine($this->root, 'config.json');
    file_put_contents($old, 'old content');

    $newDir = dirname(ConfigPath::get());
    if (file_exists($newDir)) {
        rmdir($newDir); // ensure directory doesn't exist
    }

    ConfigPath::ensureMigrated();

    expect(is_dir($newDir))->toBeTrue();
    expect(file_get_contents(ConfigPath::get()))->toBe('old content');
});
