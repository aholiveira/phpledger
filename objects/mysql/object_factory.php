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
    private static ?\mysqli $_dblink = null;
    private Logger $logger;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? new Logger("ledger.log");
    }
    private static function connect(): \mysqli
    {
        if (static::$_dblink instanceof \mysqli) {
            return static::$_dblink;
        }
        $host = config::get("host");
        $dbase = config::get("database");
        $user = config::get("user");
        $pass = config::get("password");
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            static::$_dblink = new \mysqli($host, $user, $pass, $dbase);
            static::$_dblink->set_charset('utf8mb4');
        } catch (\Exception $ex) {
            static::handleError($ex);
            exit();
        }
        return static::$_dblink;
    }
    public static function handleError(\Exception $ex)
    {
        global $logger;
        print "<p>Error while connecting to the database. Please check config file.</p>";
        $logger->error("Error [{$ex->getMessage()}] while connecting to the database. Please check config file.");
    }
    public static function dataStorage(): DataStorageInterface
    {
        return new MySqlStorage();
    }
    public static function account(): account
    {
        return new account(ObjectFactory::connect());
    }
    public static function accountType(): accounttype
    {
        account::class;
        return new accounttype(ObjectFactory::connect());
    }
    public static function currency(): currency
    {
        return new currency(ObjectFactory::connect());
    }
    public static function defaults(): defaults
    {
        return new defaults(ObjectFactory::connect());
    }
    public static function entryCategory(): EntryCategory
    {
        return new EntryCategory(ObjectFactory::connect());
    }
    public static function ledger(): ledger
    {
        return new ledger(ObjectFactory::connect());
    }
    public static function ledgerEntry(): ledgerentry
    {
        return new ledgerentry(ObjectFactory::connect());
    }
    public static function reportMonth(): ReportMonth
    {
        return new ReportMonth(ObjectFactory::connect());
    }
    public static function reportYear(): ReportYear
    {
        return new ReportYear(ObjectFactory::connect());
    }
    public static function user(): user
    {
        return new user(ObjectFactory::connect());
    }
}
