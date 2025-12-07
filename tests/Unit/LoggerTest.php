<?php

use PHPLedger\Contracts\LogLevel;
use PHPLedger\Util\Logger;
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
    $file = Path::combine(__DIR__, 'tmp', 'auto', 'logs', 'auto.log');
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

it('singleton instance returns Logger object', function () {
    $logger1 = Logger::instance();
    $logger2 = Logger::instance();
    expect($logger1)->toBeInstanceOf(Logger::class);
    expect($logger1)->toBe($logger2); // same instance
});

it('init sets a new singleton instance', function () {
    $file = Path::combine($this->logDir, 'init.log');
    Logger::init($file, LogLevel::INFO);
    $logger = Logger::instance();
    $logger->info('Init test');
    $content = file_get_contents($file);
    expect($content)->toContain('Init test');
});

it('setLogLevel updates the current log level', function () {
    $logger = new Logger($this->logFile, LogLevel::ERROR);
    $logger->setLogLevel(LogLevel::DEBUG);
    $logger->debug('Debug after level change');
    $content = file_get_contents($this->logFile);
    expect($content)->toContain('Debug after level change');
});
