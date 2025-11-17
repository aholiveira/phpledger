<?php
namespace PHPLedger\Storage\Abstract;

use Exception;
use \PHPLedger\Contracts\DataObjectFactoryInterface;
use \PHPLedger\Contracts\DataStorageInterface;
use \PHPLedger\Domain\Account;
use \PHPLedger\Domain\AccountType;
use \PHPLedger\Domain\Currency;
use \PHPLedger\Domain\Defaults;
use \PHPLedger\Domain\EntryCategory;
use \PHPLedger\Domain\Ledger;
use \PHPLedger\Domain\LedgerEntry;
use \PHPLedger\Domain\ReportMonth;
use \PHPLedger\Domain\ReportYear;
use \PHPLedger\Domain\User;
use \PHPLedger\Util\Logger;

abstract class AbstractObjectFactory implements DataObjectFactoryInterface
{
    private static ?DataObjectFactoryInterface $backendFactory = null;
    private static ?Logger $logger = null;
    public static function init(string $backend = "mysql", ?Logger $logger = null): void
    {
        if (static::$backendFactory === null) {
            switch ($backend) {
                case "mysql":
                    static::$backendFactory = new \PHPLedger\Storage\MySql\MySqlObjectFactory($backend, $logger);
                    break;
                default:
                    throw new Exception("Storage not implemented");
            }
        }
        static::$logger = $logger ?? new Logger("ledger.log");
    }
    public static function dataStorage(): DataStorageInterface
    {
        static::init();
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
    public static function reportMonth(): ReportMonth
    {
        return static::$backendFactory::ReportMonth();
    }
    public static function reportYear(): ReportYear
    {
        return static::$backendFactory::ReportYear();
    }
    public static function user(): User
    {
        return static::$backendFactory::user();
    }
}
