<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Contracts;

use PHPLedger\Contracts\Domain\DefaultsObjectInterface;
use PHPLedger\Contracts\Domain\LedgerEntryObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;

interface DataObjectFactoryInterface
{
    public static function dataStorage(): DataStorageInterface;
    public static function defaults(): DefaultsObjectInterface;
    public static function user(): UserObjectInterface;
    public static function account(): DataObjectInterface;
    public static function accountType(): DataObjectInterface;
    public static function currency(): DataObjectInterface;
    public static function ledger(): DataObjectInterface;
    public static function ledgerEntry(): LedgerEntryObjectInterface;
    public static function entryCategory(): DataObjectInterface;
}
