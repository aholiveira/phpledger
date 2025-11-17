<?php
namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class LedgerEntry extends AbstractDataObject implements DataObjectInterface
{
    public string $entry_date;
    public int $category_id;
    public EntryCategory $category;
    public float $currency_amount;
    public string $currency_id;
    public Currency $currency;
    public float $euro_amount;
    public float $exchange_rate;
    public int $account_id;
    public ?Account $account;
    public int $direction;
    public ?string $remarks;
    public string $username = "";
    public string $created_at;
    public string $updated_at;
    public int $ledger_id;
    abstract public function getBalanceBeforeDate($date, $account_id = null): ?float;
    abstract public static function getList(array $field_filter = []): array;
    abstract public static function getById($id): ?ledgerentry;

}
