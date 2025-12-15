<?php

namespace PHPLedger\Storage\Abstract;

use PHPLedger\Contracts\DataObjectInterface;

abstract class AbstractDataObject implements DataObjectInterface
{
    public ?int $id;
    protected static string $errorMessage;
    abstract public function validate(): bool;
    abstract public function errorMessage(): string;
    abstract public function update(): bool;
    abstract public function delete(): bool;
    abstract public static function getNextId(): int;
    abstract public static function getList(array $fieldFilter = []): array;
    abstract public static function getById(int $id): ?DataObjectInterface;

    public function getId(): ?int
    {
        return $this->id;
    }
}
