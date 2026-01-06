<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Domain;

use DateTimeInterface;
use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;

abstract class Account extends AbstractDataObject implements DataObjectInterface
{
    public string $name = "";
    public string $number = "";
    public string $iban = "";
    public string $swift = "";
    public int $grupo = 0;
    public int $typeId;
    public string $openDate;
    public string $closeDate;
    public int $active;
    public function getBalanceOnDate(DateTimeInterface $date): array
    {
        return $this->getBalance(null, $date);
    }
    abstract public function getBalance(?DateTimeInterface $startDate = null, ?DateTimeInterface $endDate = null): array;
}
