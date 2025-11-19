<?php
namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class LedgerEntry extends AbstractDataObject implements DataObjectInterface
{
    public string $entry_date;
    public int $categoryId;
    public EntryCategory $category;
    public float $currencyAmount;
    public string $currency_id;
    public Currency $currency;
    public float $euroAmount;
    public float $exchangeRate;
    public int $accountId;
    public ?Account $account;
    public int $direction;
    public ?string $remarks;
    public string $username = "";
    public string $createdAt;
    public string $updatedAt;
    public int $ledger_id;
    abstract public function getBalanceBeforeDate($date, $accountId = null): ?float;
    abstract public static function getList(array $fieldFilter = []): array;
    abstract public static function getById($id): ?ledgerentry;

}
