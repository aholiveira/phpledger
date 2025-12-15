<?php

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

interface LedgerEntryObjectInterface extends DataObjectInterface
{
    public function getBalanceBeforeDate($date, $accountId = null): ?float;
    public static function getList(array $fieldFilter = []): array;
    public static function getById($id): ?self;
}
