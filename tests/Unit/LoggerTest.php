<?php

use PHPLedger\Util\Logger;
use PHPLedger\Util\LogLevel;

beforeEach(function () {
    $this->logDir = __DIR__ . '/tests/tmp/logs';
    $this->logFile = $this->logDir . '/test.log';

    if (is_dir($this->logDir)) {
        array_map('unlink', glob($this->logDir . '/*'));
    } else {
        mkdir($this->logDir, 0750, true);
    }
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
    $file = __DIR__ . '/tests/tmp/auto/logs/auto.log';
    $dir = dirname($file);
    if (is_dir($dir)) {
        array_map('unlink', glob($dir . '/*'));
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
