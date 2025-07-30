<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
interface iobject_factory
{
    public static function data_storage(): idata_storage;
    public static function defaults(): iobject;
    public static function user(): iobject;
    public static function account(): iobject;
    public static function accounttype(): iobject;
    public static function currency(): iobject;
    public static function ledger(): iobject;
    public static function ledgerentry(): iobject;
    public static function entry_category(): iobject;
    public static function report_month(): ireport;
    public static function report_year(): ireport;
}
