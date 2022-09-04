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
    private $_dblink;
    private $_config;
    public function __construct(config $config)
    {
        $host = $config->getParameter("host");
        $dbase = $config->getParameter("database");
        $user = $config->getParameter("user");
        $pass = $config->getParameter("password");
        $this->_config = $config;
        try {
            $this->_dblink = @new \mysqli($host, $user, $pass, $dbase);
            if ($this->_dblink->connect_errno) {
                throw new \RuntimeException('mysqli connection error: ' . $this->_dblink->connect_error);
            }
        } catch (\Exception $ex) {
            print_var($this, "THIS", true);
            print_var($this->_dblink, "DBLINK", true);
            print_var($ex, "EXCEPTION", true);
            #debug_print_backtrace();
            #debug_print($ex->getMessage());
            #debug_print($ex->getTraceAsString());
        }
    }
    public function data_storage(): idata_storage
    {
        return new mysql_storage($this->_config);
    }
    public function account(): account
    {
        return new account($this->_dblink);
    }
    public function accounttype(): accounttype
    {
        return new accounttype($this->_dblink);
    }
    public function currency(): currency
    {
        return new currency($this->_dblink);
    }
    public function defaults(): defaults
    {
        return new defaults($this->_dblink);
    }
    public function entry_category(): entry_category
    {
        return new entry_category($this->_dblink);
    }
    public function ledger(): ledger
    {
        return new ledger($this->_dblink);
    }
    public function ledgerentry(): ledgerentry
    {
        return new ledgerentry($this->_dblink);
    }
    public function report_month(): report_month
    {
        return new report_month($this->_dblink);
    }
    public function report_year(): report_year
    {
        return new report_year($this->_dblink);
    }
    public function user(): user
    {
        return new user($this->_dblink);
    }
}
