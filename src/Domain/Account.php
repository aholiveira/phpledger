<?php
namespace PHPLedger\Domain;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Storage\Abstract\AbstractDataObject;
abstract class Account extends AbstractDataObject implements DataObjectInterface
{
    public string $name = "";
    public string $number = "";
    public string $iban = "";
    public string $swift = "";
    public int $group = 0;
    public int $type_id;
    public string $open_date;
    public string $close_date;
    public int $active;
    public function getBalanceOnDate(\DateTimeInterface $date): array
    {
        return $this->getBalance(null, $date);
    }
    abstract public function getBalance(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array;

}
