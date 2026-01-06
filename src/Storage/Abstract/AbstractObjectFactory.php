<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\Abstract;

use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\DataStorageInterface;
use PHPLedger\Domain\Account;
use PHPLedger\Domain\AccountType;
use PHPLedger\Domain\Currency;
use PHPLedger\Domain\Defaults;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Domain\Ledger;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Domain\User;
use UnexpectedValueException;

abstract class AbstractObjectFactory implements DataObjectFactoryInterface
{
    protected static ?DataObjectFactoryInterface $backendFactory = null;
    public static function init(string $backend = "mysql"): void
    {
        if (static::$backendFactory !== null) {
            return;
        }
        switch ($backend) {
            case 'mysql':
                static::$backendFactory = new \PHPLedger\Storage\MySql\MySqlObjectFactory($backend);
                break;
            case '':
                break;
            default:
                throw new UnexpectedValueException("Storage not implemented");
                break;
        }
    }
    public static function dataStorage(): DataStorageInterface
    {
        return static::$backendFactory::dataStorage();
    }
    public static function account(): Account
    {
        return static::$backendFactory::account();
    }
    public static function accountType(): AccountType
    {
        return static::$backendFactory::accounttype();
    }
    public static function currency(): Currency
    {
        return static::$backendFactory::currency();
    }
    public static function defaults(): Defaults
    {
        return static::$backendFactory::defaults();
    }
    public static function entryCategory(): EntryCategory
    {
        return static::$backendFactory::EntryCategory();
    }
    public static function ledger(): Ledger
    {
        return static::$backendFactory::ledger();
    }
    public static function ledgerEntry(): LedgerEntry
    {
        return static::$backendFactory::ledgerentry();
    }
    public static function user(): User
    {
        return static::$backendFactory::user();
    }
}
