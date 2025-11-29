<?php

namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class Defaults extends AbstractDataObject implements DataObjectInterface
{
    public int $categoryId;
    public int $accountId;
    public string $currencyId;
    public string $entryDate;
    public int $direction;
    public ?string $language;
    public ?string $username;
    public ?string $lastVisited;
    public ?int $showReportGraph;
    abstract public static function getByUsername(string $username): ?Defaults;
    abstract public static function init(): Defaults;
}
