<?php

/**
 * Interface for data storage objects.
 *
 * Provides methods to retrieve and store messages such as errors,
 * warnings, or informational notes associated with the data object.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface DataStorageInterface
{
    /**
     * Get the current message stored in the object.
     *
     * @return string The stored message
     */
    public function message(): string;

    /**
     * Add a new message to the object.
     *
     * @param string $message Message to add
     * @return string The current message after adding the new message
     */
    public function addMessage(string $message): string;
}
