<?php
namespace PHPLedger\Views;
if (!\defined("BACKEND")) {
    die("This file should only be included!");
}

include_once VIEWS_DIR . "/ObjectViewer.php";
include_once VIEWS_DIR . "/accountBalanceView.php";
include_once VIEWS_DIR . "/accountTypeView.php";
include_once VIEWS_DIR . "/accountView.php";
include_once VIEWS_DIR . "/currencyView.php";
include_once VIEWS_DIR . "/entryCategoryView.php";
include_once VIEWS_DIR . "/ledgerEntryView.php";
include_once VIEWS_DIR . "/reportHtmlView.php";
include_once VIEWS_DIR . "/reportMonthHtmlView.php";
include_once VIEWS_DIR . "/reportYearHtmlView.php";

/**
 * Factory for viewer objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Domain\Account;
use PHPLedger\Domain\AccountType;
use PHPLedger\Domain\Currency;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Domain\ReportMonth;
use PHPLedger\Domain\ReportYear;
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
    public function accountBalanceView(account $object): \accountBalanceView
    {
        return new \accountBalanceView($object);
    }
    public function accountTypeView(accounttype $object): \accountTypeView
    {
        return new \accountTypeView($object);
    }
    public function accountView(account $object): \accountView
    {
        return new \accountView($object);
    }
    public function currencyView(currency $object): \currencyView
    {
        return new \currencyView($object);
    }
    public function entryCategoryView(EntryCategory $object): \entryCategoryView
    {
        return new \entryCategoryView($object);
    }
    public function ledgerEntryView(ledgerentry $object): \ledgerEntryView
    {
        return new \ledgerEntryView($object);
    }
    public function reportMonthHtmlView(ReportMonth $object): \reportMonthHtmlView
    {
        return new \reportMonthHtmlView($object);
    }
    public function reportYearHtmlView(ReportYear $object): \reportYearHtmlView
    {
        return new \reportYearHtmlView($object);
    }
}
