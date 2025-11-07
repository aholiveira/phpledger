<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
interface iObjectFactory
{
    public static function dataStorage(): iDataStorage;
    public static function defaults(): iObject;
    public static function user(): iObject;
    public static function account(): iObject;
    public static function accountType(): iObject;
    public static function currency(): iObject;
    public static function ledger(): iObject;
    public static function ledgerEntry(): iObject;
    public static function entryCategory(): iObject;
    public static function reportMonth(): iReport;
    public static function reportYear(): iReport;
}
