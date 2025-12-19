<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\LoggerServiceInterface;
use PHPLedger\Contracts\LogLevel;

class Logger implements LoggerServiceInterface
{
    private string $logFile;
    private LogLevel $logLevel;
    /**
     * Constructor for Logger.
     * @param string $file
     * @param LogLevel $logLevel
     */
    public function __construct(string $file, LogLevel $logLevel = LogLevel::DEBUG)
    {
        $this->logFile = $file;
        $this->logLevel = $logLevel;
    }
    /**
     * Set the log level.
     * @param LogLevel $logLevel
     */
    public function setLogLevel(LogLevel $logLevel): void
    {
        $this->logLevel = $logLevel;
    }
    /**
     * Log a debug message.
     * @param string $message
     * @param string $prefix
     */
    public function debug(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::DEBUG, $message, $prefix);
    }
    /**
     * Log an info message.
     * @param string $message
     * @param string $prefix
     */
    public function info(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::INFO, $message, $prefix);
    }
    /**
     * Log a notice message.
     * @param string $message
     * @param string $prefix
     */
    public function notice(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::NOTICE, $message, $prefix);
    }
    /**
     * Log a warning message.
     * @param string $message
     * @param string $prefix
     */
    public function warning(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::WARNING, $message, $prefix);
    }
    /**
     * Log an error message.
     * @param string $message
     * @param string $prefix
     */
    public function error(string $message, string $prefix = ""): void
    {
        $this->writeLog(LogLevel::ERROR, $message, $prefix);
    }
    /**
     * Dump arbitrary data to the log.
     * @param mixed $data
     * @param string $prefix
     */
    public function dump(mixed $data, string $prefix = ""): void
    {
        $output = print_r($data, true);
        $this->writeLog(LogLevel::DEBUG, $output, $prefix);
    }
    /**
     * Dump the current stack trace to the log.
     */
    public function dumpStack(): void
    {
        $this->dump(debug_backtrace());
    }
    /**
     * Write a log entry to the log file.
     * @param LogLevel $level
     * @param string $message
     * @param string $prefix
     */
    private function writeLog(LogLevel $level, string $message, string $prefix = ""): void
    {
        if ($level->value > $this->logLevel->value) {
            return;
        }
        if (empty($this->logFile)) {
            return;
        }
        $prefix = trim($prefix);
        $prefix = $prefix !== '' ? $prefix : '-';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] [{$level->name}] [$prefix] $message" . PHP_EOL;
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
