<?php
namespace PHPLedger\Views;
if (!\defined("BACKEND")) {
    die("This file should only be included!");
}

include_once VIEWS_DIR . "/object_viewer.php";
include_once VIEWS_DIR . "/account_balance_view.php";
include_once VIEWS_DIR . "/account_type_view.php";
include_once VIEWS_DIR . "/account_view.php";
include_once VIEWS_DIR . "/currency_view.php";
include_once VIEWS_DIR . "/entry_category_view.php";
include_once VIEWS_DIR . "/ledger_entry_view.php";
include_once VIEWS_DIR . "/report_view.php";
include_once VIEWS_DIR . "/report_month_view.php";
include_once VIEWS_DIR . "/report_year_view.php";

/**
 * Factory for viewer objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use \PHPLedger\Domain\Account;
use \PHPLedger\Domain\AccountType;
use \PHPLedger\Domain\Currency;
use \PHPLedger\Domain\EntryCategory;
use \PHPLedger\Domain\LedgerEntry;
use \PHPLedger\Domain\ReportMonth;
use \PHPLedger\Domain\ReportYear;
class ViewFactory
{
    private static ?ViewFactory $viewFactory = null;
    public static function instance(): self
    {
        if (self::$viewFactory === null) {
            self::$viewFactory = new ViewFactory();
        }
        return static::$viewFactory;
    }
    public function account_balance_view(account $object): \account_balance_view
    {
        return new \account_balance_view($object);
    }
    public function account_type_view(accounttype $object): \account_type_view
    {
        return new \account_type_view($object);
    }
    public function account_view(account $object): \account_view
    {
        return new \account_view($object);
    }
    public function currency_view(currency $object): \currency_view
    {
        return new \currency_view($object);
    }
    public function entry_category_view(EntryCategory $object): \entry_category_view
    {
        return new \entry_category_view($object);
    }
    public function ledger_entry_view(ledgerentry $object): \ledger_entry_view
    {
        return new \ledger_entry_view($object);
    }
    public function report_month_view(ReportMonth $object): \report_month_HtmlView
    {
        return new \report_month_HtmlView($object);
    }
    public function report_year_view(ReportYear $object): \report_year_HtmlView
    {
        return new \report_year_HtmlView($object);
    }
}
