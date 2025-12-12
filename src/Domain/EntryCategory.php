<?php

namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class EntryCategory extends AbstractDataObject implements DataObjectInterface
{
    public ?string $description;
    public int $active;
    public ?int $parentId;
    public ?string $parentDescription = null;
    public array $children;
    public string $validationMessage;
    abstract public function getBalance(): float;
    public function __construct()
    {
        if (!isset($this->parentId) || $this->parentId === null) {
            $this->parentId = 0;
        }
    }
}
