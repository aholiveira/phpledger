<?php

/**
 * Factory interface for creating and accessing data objects.
 *
 * Provides methods to retrieve domain-specific objects such as users,
 * accounts, ledger entries, defaults, currencies, and categories.
 * Serves as a central access point for data storage and object retrieval.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

use PHPLedger\Contracts\Domain\DefaultsObjectInterface;
use PHPLedger\Contracts\Domain\LedgerEntryObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;

interface DataObjectFactoryInterface
{
    /**
     * Get the underlying data storage service.
     *
     * @return DataStorageInterface
     */
    public static function dataStorage(): DataStorageInterface;

    /**
     * Get the Defaults domain object.
     *
     * @return DefaultsObjectInterface
     */
    public static function defaults(): DefaultsObjectInterface;

    /**
     * Get the User domain object.
     *
     * @return UserObjectInterface
     */
    public static function user(): UserObjectInterface;

    /**
     * Get the Account data object.
     *
     * @return DataObjectInterface
     */
    public static function account(): DataObjectInterface;

    /**
     * Get the Account Type data object.
     *
     * @return DataObjectInterface
     */
    public static function accountType(): DataObjectInterface;

    /**
     * Get the Currency data object.
     *
     * @return DataObjectInterface
     */
    public static function currency(): DataObjectInterface;

    /**
     * Get the Ledger data object.
     *
     * @return DataObjectInterface
     */
    public static function ledger(): DataObjectInterface;

    /**
     * Get the Ledger Entry domain object.
     *
     * @return LedgerEntryObjectInterface
     */
    public static function ledgerEntry(): LedgerEntryObjectInterface;

    /**
     * Get the Entry Category data object.
     *
     * @return DataObjectInterface
     */
    public static function entryCategory(): DataObjectInterface;
}
