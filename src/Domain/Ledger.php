<?php
namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class Ledger extends AbstractDataObject implements DataObjectInterface
{
    public string $name;
}
