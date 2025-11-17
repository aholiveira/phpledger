<?php
namespace PHPLedger\Domain;

use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class Defaults extends AbstractDataObject implements DataObjectInterface
{
    public int $category_id;
    public int $account_id;
    public string $currency_id;
    public string $entry_date;
    public int $direction;
    public ?string $language;
    public ?string $username;
    public ?string $last_visited;
    abstract public static function getByUsername(string $username): ?Defaults;
    abstract public static function init(): defaults;

}
