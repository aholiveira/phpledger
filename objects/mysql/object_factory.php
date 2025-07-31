<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/mysql_object.php";
include __DIR__ . "/account.php";
include __DIR__ . "/accounttype.php";
include __DIR__ . "/mysql_storage.php";
include __DIR__ . "/currency.php";
include __DIR__ . "/defaults.php";
include __DIR__ . "/entry_category.php";
include __DIR__ . "/ledger.php";
include __DIR__ . "/ledgerentry.php";
include __DIR__ . "/report.php";
include __DIR__ . "/reportmonth.php";
include __DIR__ . "/reportyear.php";
include __DIR__ . "/user.php";

class object_factory implements iobject_factory
{
    private static ?\mysqli $_dblink = null;
    public function __construct()
    {
    }
    private static function connect(): mysqli
    {
        if (static::$_dblink instanceof mysqli) {
            return static::$_dblink;
        }
        $host = config::get("host");
        $dbase = config::get("database");
        $user = config::get("user");
        $pass = config::get("password");
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            static::$_dblink = @new \mysqli($host, $user, $pass, $dbase);
            static::$_dblink->set_charset('utf8mb4');
        } catch (\Exception $ex) {
            static::handle_error($ex);
            exit();
        }
        return static::$_dblink;
    }
    public static function handle_error(\Exception $ex)
    {
        global $logger;
        print "<p>Error while connecting to the database. Please check config file.</p>";
        $logger->error("Error [{$ex->getMessage()}] while connecting to the database. Please check config file.");
    }
    public static function data_storage(): idata_storage
    {
        return new mysql_storage();
    }
    public static function account(): account
    {
        return new account(object_factory::connect());
    }
    public static function accounttype(): accounttype
    {
        return new accounttype(object_factory::connect());
    }
    public static function currency(): currency
    {
        return new currency(object_factory::connect());
    }
    public static function defaults(): defaults
    {
        return new defaults(object_factory::connect());
    }
    public static function entry_category(): entry_category
    {
        return new entry_category(object_factory::connect());
    }
    public static function ledger(): ledger
    {
        return new ledger(object_factory::connect());
    }
    public static function ledgerentry(): ledgerentry
    {
        return new ledgerentry(object_factory::connect());
    }
    public static function report_month(): report_month
    {
        return new report_month(object_factory::connect());
    }
    public static function report_year(): report_year
    {
        return new report_year(object_factory::connect());
    }
    public static function user(): user
    {
        return new user(object_factory::connect());
    }
}
