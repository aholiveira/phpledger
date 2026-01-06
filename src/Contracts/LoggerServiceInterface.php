<?php

/**
 * Interface for logging services.
 *
 * Provides methods for logging messages at various levels,
 * dumping arbitrary data, and recording stack traces.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface LoggerServiceInterface
{
    /**
     * Set the logging level.
     *
     * @param LogLevel $logLevel
     */
    public function setLogLevel(LogLevel $logLevel): void;

    /**
     * Log a debug-level message.
     *
     * @param string $message
     * @param string $prefix Optional prefix for the message
     */
    public function debug(string $message, string $prefix = ""): void;

    /**
     * Log an info-level message.
     *
     * @param string $message
     * @param string $prefix Optional prefix for the message
     */
    public function info(string $message, string $prefix = ""): void;

    /**
     * Log a notice-level message.
     *
     * @param string $message
     * @param string $prefix Optional prefix for the message
     */
    public function notice(string $message, string $prefix = ""): void;

    /**
     * Log a warning-level message.
     *
     * @param string $message
     * @param string $prefix Optional prefix for the message
     */
    public function warning(string $message, string $prefix = ""): void;

    /**
     * Log an error-level message.
     *
     * @param string $message
     * @param string $prefix Optional prefix for the message
     */
    public function error(string $message, string $prefix = ""): void;

    /**
     * Dump arbitrary data to the log.
     *
     * @param mixed  $data
     * @param string $prefix Optional prefix for the data
     */
    public function dump(mixed $data, string $prefix = ""): void;

    /**
     * Dump the current stack trace to the log.
     */
    public function dumpStack(): void;
}
