<?php

if (!defined("BACKEND") || !defined("OBJECTS_DIR")) {
    die("This file should only be included!");
}

include VIEWS_DIR . "/object_viewer.php";
include VIEWS_DIR . "/account_balance_view.php";
include VIEWS_DIR . "/account_type_view.php";
include VIEWS_DIR . "/account_view.php";
include VIEWS_DIR . "/currency_view.php";
include VIEWS_DIR . "/entry_category_view.php";
include VIEWS_DIR . "/ledger_entry_view.php";
include VIEWS_DIR . "/report_view.php";
include VIEWS_DIR . "/report_month_view.php";
include VIEWS_DIR . "/report_year_view.php";

/**
 * Factory for viewer objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class view_factory
{
    public function account_balance_view(account $object): account_balance_view
    {
        return new account_balance_view($object);
    }
    public function account_type_view(accounttype $object): account_type_view
    {
        return new account_type_view($object);
    }
    public function account_view(account $object): account_view
    {
        return new account_view($object);
    }
    public function currency_view(currency $object): currency_view
    {
        return new currency_view($object);
    }
    public function entry_category_view(entry_category $object): entry_category_view
    {
        return new entry_category_view($object);
    }
    public function ledger_entry_view(ledgerentry $object): ledger_entry_view
    {
        return new ledger_entry_view($object);
    }
    public function report_month_view(report_month $object): report_month_HtmlView
    {
        return new report_month_HtmlView($object);
    }
    public function report_year_view(report_year $object): report_year_HtmlView
    {
        return new report_year_HtmlView($object);
    }
}
