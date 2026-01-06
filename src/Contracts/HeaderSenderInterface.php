<?php

/**
 * Interface for sending HTTP headers.
 *
 * Provides methods to send headers and check if headers have already been sent.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface HeaderSenderInterface
{
    /**
     * Send an HTTP header.
     *
     * @param string $header  The header string to send
     * @param bool   $replace Whether to replace a previous header with the same name
     * @param int    $code    HTTP response code
     */
    public function send(string $header, bool $replace = true, int $code = 0): void;

    /**
     * Check if headers have already been sent.
     *
     * @return bool True if headers sent, false otherwise
     */
    public function sent(): bool;
}
