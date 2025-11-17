<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Contracts;
interface DataObjectFactoryInterface
{
    public static function dataStorage(): DataStorageInterface;
    public static function defaults(): DataObjectInterface;
    public static function user(): DataObjectInterface;
    public static function account(): DataObjectInterface;
    public static function accountType(): DataObjectInterface;
    public static function currency(): DataObjectInterface;
    public static function ledger(): DataObjectInterface;
    public static function ledgerEntry(): DataObjectInterface;
    public static function entryCategory(): DataObjectInterface;
    public static function reportMonth(): ReportInterface;
    public static function reportYear(): ReportInterface;
}
