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
    static function data_storage(): idata_storage;
    static function defaults(): iobject;
    static function user(): iobject;
    static function account(): iobject;
    static function accounttype(): iobject;
    static function currency(): iobject;
    static function ledger(): iobject;
    static function ledgerentry(): iobject;
    static function entry_category(): iobject;
    static function report_month(): ireport;
    static function report_year(): ireport;
}
