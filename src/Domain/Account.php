<?php

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
    public int $activa;
    public function getBalanceOnDate(DateTimeInterface $date): array
    {
        return $this->getBalance(null, $date);
    }
    abstract public function getBalance(?DateTimeInterface $startDate = null, ?DateTimeInterface $endDate = null): array;
}
