<?php

/**
 * Generic data object interface.
 *
 * Common interface for all data objects, defining standard CRUD operations,
 * validation, error handling, and object retrieval methods.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface DataObjectInterface
{
    /**
     * Validate the data object.
     *
     * @return bool True if valid, false otherwise
     */
    public function validate(): bool;

    /**
     * Get the last error message.
     *
     * @return string
     */
    public function errorMessage(): string;

    /**
     * Create a new instance in storage.
     *
     * @return self
     */
    public function create(): self;

    /**
     * Read an object by its ID.
     *
     * @param int $id
     * @return self|null
     */
    public function read(int $id): ?self;

    /**
     * Update the object in storage.
     *
     * @return bool True on success
     */
    public function update(): bool;

    /**
     * Delete the object from storage.
     *
     * @return bool True on success
     */
    public function delete(): bool;

    /**
     * Get the next available ID for this object type.
     *
     * @return int
     */
    public static function getNextId(): int;

    /**
     * Retrieve a list of objects optionally filtered by fields.
     *
     * @param array $fieldFilter
     * @return array List of objects
     */
    public static function getList(array $fieldFilter = []): array;

    /**
     * Retrieve an object by its ID.
     *
     * @param int $id
     * @return self|null
     */
    public static function getById(int $id): ?self;
}
