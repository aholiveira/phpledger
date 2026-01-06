<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class AccountType extends AbstractDataObject implements DataObjectInterface
{
    public ?string $description = null;
    public int $savings = 0;
    public function __construct()
    {
        if (!isset($this->id) || $this->id === null) {
            $this->id = 0;
        }
    }
}
