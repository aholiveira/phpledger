<?php
namespace PHPLedger\Domain;

use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class Currency extends AbstractDataObject implements DataObjectInterface
{
    public string $code;
    public string $description;
    public float $exchange_rate;
    public string $username = "";
    public string $created_at;
    public string $updated_at;
}
