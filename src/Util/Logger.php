<?php

namespace PHPLedger\Util;

enum LogLevel: int
{
    case ERROR = 0;
    case WARNING = 1;
    case NOTICE = 2;
    case INFO = 3;
    case DEBUG = 4;
}

class Logger
{
    private string $logFile;
    private LogLevel $logLevel;
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self(ROOT_DIR . "/logs/ledger.log");
    }
    public function __construct(string $file, LogLevel $logLevel = LogLevel::DEBUG)
    {
        $this->logFile = $file;
        $this->logLevel = $logLevel;
    }
    public function setLogLevel(LogLevel $logLevel)
    {
        $this->logLevel = $logLevel;
    }
    public function debug(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::DEBUG, $message, $prefix);
    }
    public function info(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::INFO, $message, $prefix);
    }
    public function notice(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::NOTICE, $message, $prefix);
    }
    public function warning(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::WARNING, $message, $prefix);
    }
    public function error(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::ERROR, $message, $prefix);
    }
    public function dump(mixed $data, string $prefix = ""): void
    {
        $output = print_r($data, true);
        $this->writeLog(LogLevel::DEBUG, $output, $prefix);
    }
    public function dumpStack(): void
    {
        $this->dump(debug_backtrace());
    }
    private function levelText(LogLevel $level): string
    {
        return match ($level) {
            LogLevel::DEBUG => "DEBUG",
            LogLevel::INFO => "INFO",
            LogLevel::NOTICE => "NOTICE",
            LogLevel::WARNING => "WARN",
            LogLevel::ERROR => "ERROR",
        };
    }
    private function writeLog(LogLevel $level, string $message, string $prefix = ""): void
    {
        if ($level->value > $this->logLevel->value) {
            return;
        }
        $prefix = trim($prefix);
        $prefix = $prefix !== '' ? $prefix : '-';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] [{$this->levelText($level)}] [$prefix] $message" . PHP_EOL;
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
