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
