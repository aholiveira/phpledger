<?php

namespace PHPLedger\Contracts;

enum LogLevel: int
{
    case ERROR = 0;
    case WARNING = 1;
    case NOTICE = 2;
    case INFO = 3;
    case DEBUG = 4;
}

interface LoggerServiceInterface
{
    /**
     * Set the log level.
     * @param LogLevel $logLevel
     */
    public function setLogLevel(LogLevel $logLevel): void;
    /**
     * Log a debug message.
     * @param string $message
     * @param string $prefix
     */
    public function debug(string $message, string $prefix = ""): void;
    /**
     * Log an info message.
     * @param string $message
     * @param string $prefix
     */
    public function info(string $message, string $prefix = ""): void;
    /**
     * Log a notice message.
     * @param string $message
     * @param string $prefix
     */
    public function notice(string $message, string $prefix = ""): void;
    /**
     * Log a warning message.
     * @param string $message
     * @param string $prefix
     */
    public function warning(string $message, string $prefix = ""): void;
    /**
     * Log an error message.
     * @param string $message
     * @param string $prefix
     */
    public function error(string $message, string $prefix = ""): void;
    /**
     * Dump arbitrary data to the log.
     * @param mixed $data
     * @param string $prefix
     */
    public function dump(mixed $data, string $prefix = ""): void;
    /**
     * Dump the current stack trace to the log.
     */
    public function dumpStack(): void;
}
