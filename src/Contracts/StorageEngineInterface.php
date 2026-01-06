<?php

/**
 * Interface for storage engine operations.
 *
 * Provides methods to test connections, create storage structures,
 * run migrations, and check for pending migrations.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface StorageEngineInterface
{
    /**
     * Test the storage engine with the given settings.
     *
     * @param array $settings Configuration settings for the engine
     * @return array Test results
     */
    public function test(array $settings): array;

    /**
     * Create storage structures using the given settings.
     *
     * @param array $settings Configuration settings for creation
     */
    public function create(array $settings): void;

    /**
     * Run database or storage migrations.
     *
     * @param array $settings Configuration settings for migrations
     */
    public function runMigrations(array $settings): void;

    /**
     * Get a list of pending migrations.
     *
     * @param array $settings Configuration settings for checking
     * @return array List of pending migrations
     */
    public function pendingMigrations(array $settings): array;
}
