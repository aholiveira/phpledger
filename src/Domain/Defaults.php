<?php

namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class Defaults extends AbstractDataObject implements DataObjectInterface
{
    /**
     * @var int store the default category ID
     */
    public int $categoryId;
    /**
     * @var int store the default account ID
     */
    public int $accountId;
    /**
     * @var string store the default currency ID
     */
    public string $currencyId;
    /**
     * @var string store the default entry date
     */
    public string $entryDate;
    /**
     * @var int store the default direction (1 for debit, -1 for credit)
     */
    public int $direction;
    /**
     * @var string store the default language
     */
    public ?string $language;
    /**
     * @var string store the default username
     */
    public ?string $username;
    /**
     * @var string store the last visited URI
     */
    public ?string $lastVisitedUri;
    /**
     * @var int store the last visited timestamp
     */
    public ?int $lastVisitedAt;
    /**
     * @var int store the flag for showing report graph
     */
    public ?int $showReportGraph;
    /**
     * Get Defaults by username
     * @param string $username
     */
    abstract public static function getByUsername(string $username): ?Defaults;
    /**
     * Initialize Defaults
     * @return Defaults
     */
    abstract public static function init(): Defaults;
}
