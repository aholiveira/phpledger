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
    function data_storage(): idata_storage;
    function defaults(): iobject;
    function user(): iobject;
    function account(): iobject;
    function accounttype(): iobject;
    function currency(): iobject;
    function ledger(): iobject;
    function ledgerentry(): iobject;
    function entry_category(): iobject;
    function report_month(): ireport;
    function report_year(): ireport;
}
