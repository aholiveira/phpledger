<?php

use PHPLedger\Util\Logger;
use PHPLedger\Util\LogLevel;
use PHPLedger\Util\Path;

beforeEach(function () {
    $this->logDir = Path::combine(__DIR__, 'tests', 'tmp', 'logs');
    $this->logFile = Path::combine($this->logDir, 'test.log');

    if (is_dir($this->logDir)) {
        array_map('unlink', glob($this->logDir . DIRECTORY_SEPARATOR . '*'));
    } else {
        mkdir($this->logDir, 0750, true);
    }
});

afterAll(function () {
    $testDir = Path::combine(__DIR__, 'tests');
    if (is_dir($testDir)) {
        // Recursively remove directory and all its contents
        removeDirectoryRecursively($testDir);
    }
});

/**
 * Recursively remove a directory and all its contents.
 */
function removeDirectoryRecursively(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = glob($dir . DIRECTORY_SEPARATOR . '*');
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if (is_dir($item)) {
            removeDirectoryRecursively($item);
        } else {
            unlink($item);
        }
    }
    rmdir($dir);
}

it('writes an info log entry', function () {
    $logger = new Logger($this->logFile);
    $logger->info('Test info', 'PFX');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[INFO]');
    expect($content)->toContain('[PFX]');
    expect($content)->toContain('Test info');
});

it('writes a debug log entry', function () {
    $logger = new Logger($this->logFile, LogLevel::DEBUG);
    $logger->debug('Test debug', 'PFX');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[DEBUG]');
    expect($content)->toContain('[PFX]');
    expect($content)->toContain('Test debug');
});

it('creates log directory automatically', function () {
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'auto' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'auto.log';
    $dir = dirname($file);
    if (is_dir($dir)) {
        array_map('unlink', glob($dir . DIRECTORY_SEPARATOR . '*'));
        rmdir($dir);
    }
    $logger = new Logger($file);
    $logger->info('Auto dir');
    expect(is_file($file))->toBeTrue();
});

it('writes prefix as dash when empty', function () {
    $logger = new Logger($this->logFile);
    $logger->info('Message', '');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[-] Message');
});

it('writes dump output', function () {
    $logger = new Logger($this->logFile);
    $logger->dump(['a' => 1]);
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('Array');
    expect($content)->toContain('[a] => 1');
});

it('writes stack dump', function () {
    $logger = new Logger($this->logFile);
    $logger->dumpStack();
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('file');
    expect($content)->toContain('function');
});

it('honours log level and skips lower severity', function () {
    $logger = new Logger($this->logFile, LogLevel::ERROR);
    $logger->error('This line should be logged');
    $logger->debug('This line should not be logged');
    $content = file_get_contents($this->logFile);
    expect($content)->not->toContain('This line should not be logged');
    expect($content)->toContain('This line should be logged');
});
