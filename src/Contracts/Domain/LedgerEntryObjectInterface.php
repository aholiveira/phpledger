<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

/**
 * Interface for ledger entry domain objects.
 *
 * Defines methods for retrieving ledger entries, balances, and lists
 * of entries, with optional filtering and date-based queries.
 */
interface LedgerEntryObjectInterface extends DataObjectInterface
{
    /**
     * Get the account balance before a specific date.
     *
     * @param mixed      $date      Date or DateTime value to calculate balance before
     * @param int|null   $accountId Optional account ID to filter balance
     * @return float|null Balance before the given date, or null if unavailable
     */
    public function getBalanceBeforeDate($date, $accountId = null): ?float;

    /**
     * Retrieve a list of ledger entries optionally filtered by fields.
     *
     * @param array $fieldFilter Associative array of field => value for filtering
     * @return array List of ledger entries
     */
    public static function getList(array $fieldFilter = []): array;

    /**
     * Retrieve a ledger entry by its ID.
     *
     * @param mixed $id Identifier of the ledger entry
     * @return self|null The ledger entry object, or null if not found
     */
    public static function getById($id): ?self;
}
