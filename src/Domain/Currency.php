<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

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
