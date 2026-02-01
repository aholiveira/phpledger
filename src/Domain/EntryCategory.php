<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class EntryCategory extends AbstractDataObject implements DataObjectInterface
{
    public ?string $description;
    public int $fixedCost;
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
        $this->fixedCost ??= 0;
    }
}
