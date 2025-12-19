<?php

use PHPLedger\Contracts\LogLevel;
use PHPLedger\Services\Logger;
use PHPLedger\Util\Path;

beforeEach(function () {
    $this->logDir = Path::combine(__DIR__, 'tmp', 'logs');
    $this->logFile = Path::combine($this->logDir, 'test.log');

    if (is_dir($this->logDir)) {
        array_map('unlink', glob($this->logDir . DIRECTORY_SEPARATOR . '*'));
    } else {
        mkdir($this->logDir, 0750, true);
    }
});

afterAll(function () {
    $tmpDir = Path::combine(__DIR__, 'tmp');
    if (is_dir($tmpDir)) {
        removeDirectoryRecursively($tmpDir);
    }
});

function removeDirectoryRecursively(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $item) {
        is_dir($item) ? removeDirectoryRecursively($item) : unlink($item);
    }
    rmdir($dir);
}

it('writes log entries for all levels', function () {
    $logger = new Logger($this->logFile, LogLevel::DEBUG);
    $logger->debug('Debug message', 'DBG');
    $logger->info('Info message', 'INF');
    $logger->notice('Notice message', 'NTC');
    $logger->warning('Warning message', 'WRN');
    $logger->error('Error message', 'ERR');

    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[DEBUG]');
    expect($content)->toContain('[DBG]');
    expect($content)->toContain('Debug message');
    expect($content)->toContain('[INFO]');
    expect($content)->toContain('[INF]');
    expect($content)->toContain('Info message');
    expect($content)->toContain('[NOTICE]');
    expect($content)->toContain('[NTC]');
    expect($content)->toContain('Notice message');
    expect($content)->toContain('[WARNING]');
    expect($content)->toContain('[WRN]');
    expect($content)->toContain('Warning message');
    expect($content)->toContain('[ERROR]');
    expect($content)->toContain('[ERR]');
    expect($content)->toContain('Error message');
});

it('writes dump output correctly', function () {
    $logger = new Logger($this->logFile);
    $logger->dump(['key' => 'value']);
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('Array');
    expect($content)->toContain('[key] => value');
});

it('writes stack trace correctly', function () {
    $logger = new Logger($this->logFile);
    $logger->dumpStack();
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('file');
    expect($content)->toContain('function');
});

it('skips logging below the current log level', function () {
    $logger = new Logger($this->logFile, LogLevel::ERROR);
    $logger->error('Logged error');
    $logger->info('Skipped info');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('Logged error');
    expect($content)->not->toContain('Skipped info');
});

it('setLogLevel changes effective level', function () {
    $logger = new Logger($this->logFile, LogLevel::ERROR);
    $logger->setLogLevel(LogLevel::DEBUG);
    $logger->debug('Debug message');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('Debug message');
});

it('creates directory if missing when writing log', function () {
    $file = Path::combine(__DIR__, 'tmp', 'autodir', 'log.log');
    $dir = dirname($file);
    if (is_dir($dir)) {
        array_map('unlink', glob($dir . DIRECTORY_SEPARATOR . '*'));
        rmdir($dir);
    }
    $logger = new Logger($file);
    $logger->info('Directory creation test');
    expect(is_dir($dir))->toBeTrue();
    expect(is_file($file))->toBeTrue();
});

it('formats log entry correctly with timestamp, level, and prefix', function () {
    $logger = new Logger($this->logFile);
    $logger->info('Format test', 'PREFIX');
    $content = file_get_contents($this->logFile);
    expect($content)->toMatch('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \[INFO\] \[PREFIX\] Format test/');
});

it('uses dash as prefix when empty', function () {
    $logger = new Logger($this->logFile);
    $logger->info('No prefix', '');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[-] No prefix');
});
