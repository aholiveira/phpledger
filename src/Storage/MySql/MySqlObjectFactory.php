<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use \PHPLedger\Contracts\DataObjectFactoryInterface;
use \PHPLedger\Contracts\DataStorageInterface;
use \PHPLedger\Util\Logger;
class MySqlObjectFactory implements DataObjectFactoryInterface
{
    private static ?\mysqli $dbConnection = null;
    private Logger $logger;

    public function __construct(string $backend = "mysql", ?Logger $logger = null)
    {

        $this->logger = $logger ?? new Logger("ledger.log");
    }
    public static function dataStorage(): DataStorageInterface
    {
        return MySqlStorage::instance();
    }
    public static function account(): MySqlAccount
    {
        return new MySqlAccount();
    }
    public static function accountType(): MySqlAccountType
    {
        return new MySqlAccountType();
    }
    public static function currency(): MySqlCurrency
    {
        return new MySqlCurrency();
    }
    public static function defaults(): MySqlDefaults
    {
        return new MySqlDefaults();
    }
    public static function entryCategory(): MySqlEntryCategory
    {
        return new MySqlEntryCategory();
    }
    public static function ledger(): MySqlLedger
    {
        return new MySqlLedger();
    }
    public static function ledgerEntry(): MySqlLedgerEntry
    {
        return new MySqlLedgerEntry();
    }
    public static function reportMonth(): MySqlReportMonth
    {
        return new MySqlReportMonth();
    }
    public static function reportYear(): MySqlReportYear
    {
        return new MySqlReportYear();
    }
    public static function user(): MySqlUser
    {
        return new MySqlUser();
    }
}
