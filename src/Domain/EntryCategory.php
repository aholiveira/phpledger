<?php
namespace PHPLedger\Domain;

use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class EntryCategory extends AbstractDataObject implements DataObjectInterface
{
    public ?string $description;
    public int $active;
    public ?int $parent_id;
    public ?string $parent_description = null;
    public array $children;
    public string $validation_message;
    abstract public function getBalance(): float;
}
