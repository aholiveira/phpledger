<?php

/**
 * Interface for URL redirection services.
 *
 * Provides a method to redirect the client to a specified URL,
 * optionally with a delay.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface RedirectorServiceInterface
{
    /**
     * Redirect to a given URL after an optional delay.
     *
     * @param string $url   The target URL
     * @param int    $delay Delay in seconds before redirecting
     */
    public function to(string $url, int $delay = 0): void;
}
