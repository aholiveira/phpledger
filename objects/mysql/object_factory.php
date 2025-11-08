<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include_once __DIR__ . "/mysql_object.php";
include_once __DIR__ . "/account.php";
include_once __DIR__ . "/accounttype.php";
include_once __DIR__ . "/mysql_storage.php";
include_once __DIR__ . "/currency.php";
include_once __DIR__ . "/defaults.php";
include_once __DIR__ . "/entry_category.php";
include_once __DIR__ . "/ledger.php";
include_once __DIR__ . "/ledgerentry.php";
include_once __DIR__ . "/report.php";
include_once __DIR__ . "/reportmonth.php";
include_once __DIR__ . "/reportyear.php";
include_once __DIR__ . "/user.php";

use PHPLedger\Contracts\DataStorageInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Util\Logger;
class ObjectFactory implements DataObjectFactoryInterface
{
    private static ?\mysqli $dbConnection = null;
    private Logger $logger;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger("ledger.log");
    }
    public static function dataStorage(): DataStorageInterface
    {
        return MySqlStorage::instance();
    }
    public static function account(): account
    {
        return new account();
    }
    public static function accountType(): accounttype
    {
        account::class;
        return new accounttype();
    }
    public static function currency(): currency
    {
        return new currency();
    }
    public static function defaults(): defaults
    {
        return new defaults();
    }
    public static function entryCategory(): EntryCategory
    {
        return new EntryCategory();
    }
    public static function ledger(): ledger
    {
        return new ledger();
    }
    public static function ledgerEntry(): ledgerentry
    {
        return new ledgerentry();
    }
    public static function reportMonth(): ReportMonth
    {
        return new ReportMonth(MySqlStorage::getConnection());
    }
    public static function reportYear(): ReportYear
    {
        return new ReportYear(MySqlStorage::getConnection());
    }
    public static function user(): user
    {
        return new user();
    }
}
