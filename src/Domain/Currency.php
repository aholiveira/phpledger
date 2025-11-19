<?php
namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class Currency extends AbstractDataObject implements DataObjectInterface
{
    public string $code;
    public string $description;
    public float $exchangeRate;
    public string $username = "";
    public string $createdAt;
    public string $updatedAt;
}
