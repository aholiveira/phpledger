<?php
/**
 * Factory for viewer objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Views;
use PHPLedger\Domain\Account;
use PHPLedger\Domain\AccountType;
use PHPLedger\Domain\Currency;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Domain\ReportMonth;
use PHPLedger\Domain\ReportYear;
use PHPLedger\Views\AccountBalanceView;
use PHPLedger\Views\AccountTypeView;
use PHPLedger\Views\AccountView;
use PHPLedger\Views\CurrencyView;
use PHPLedger\Views\EntryCategoryView;
use PHPLedger\Views\ReportMonthHtmlView;
use PHPLedger\Views\ReportYearHtmlView;
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
    public function accountBalanceView(Account $object): AccountBalanceView
    {
        return new AccountBalanceView($object);
    }
    public function accountTypeView(AccountType $object): AccountTypeView
    {
        return new AccountTypeView($object);
    }
    public function accountView(Account $object): AccountView
    {
        return new AccountView($object);
    }
    public function currencyView(Currency $object): CurrencyView
    {
        return new CurrencyView($object);
    }
    public function entryCategoryView(EntryCategory $object): EntryCategoryView
    {
        return new EntryCategoryView($object);
    }
    public function reportMonthHtmlView(ReportMonth $object): ReportMonthHtmlView
    {
        return new ReportMonthHtmlView($object);
    }
    public function reportYearHtmlView(ReportYear $object): ReportYearHtmlView
    {
        return new ReportYearHtmlView($object);
    }
}
