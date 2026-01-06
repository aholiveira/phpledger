<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Domain;

use DateTimeImmutable;
use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Domain\Account;

class DummyAccount extends Account
{
    public function getBalance(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        return ['start' => $startDate?->format('Y-m-d') ?? null, 'end' => $endDate?->format('Y-m-d') ?? null];
    }

    // Implement abstract methods from AbstractDataObject
    public function validate(): bool
    {
        return true;
    }
    public function errorMessage(): string
    {
        return '';
    }
    public function create(): DataObjectInterface
    {
        throw new \Exception('Not implemented');
    }
    public function read(int $id): ?DataObjectInterface
    {
        return null;
    }
    public function update(): bool
    {
        return true;
    }
    public function delete(): bool
    {
        return true;
    }
    public static function init(): static
    {
        return new static();
    }
    public static function getById(int $id): ?static
    {
        return new static();
    }
    public static function defaults(): static
    {
        return new static();
    }
    public static function getNextId(): int
    {
        return 1;
    }
    public static function getList(array $fieldFilter = []): array
    {
        return [];
    }
}

it('allows setting and getting basic properties', function () {
    $acc = new DummyAccount();
    $acc->name = 'Test';
    $acc->number = '123';
    $acc->iban = 'IBAN123';
    $acc->swift = 'SWIFT123';
    $acc->grupo = 2;
    $acc->typeId = 1;
    $acc->openDate = '2025-01-01';
    $acc->closeDate = '2025-12-31';
    $acc->active = 1;

    expect($acc->name)->toBe('Test');
    expect($acc->number)->toBe('123');
    expect($acc->iban)->toBe('IBAN123');
    expect($acc->swift)->toBe('SWIFT123');
    expect($acc->grupo)->toBe(2);
    expect($acc->typeId)->toBe(1);
    expect($acc->openDate)->toBe('2025-01-01');
    expect($acc->closeDate)->toBe('2025-12-31');
    expect($acc->active)->toBe(1);
});

it('returns balance for a given date via getBalanceOnDate', function () {
    $acc = new DummyAccount();
    $date = new DateTimeImmutable('2025-06-15');
    $balance = $acc->getBalanceOnDate($date);

    expect($balance['start'])->toBeNull();
    expect($balance['end'])->toBe('2025-06-15');
});

it('getBalance returns expected start and end dates', function () {
    $acc = new DummyAccount();
    $start = new DateTimeImmutable('2025-01-01');
    $end = new DateTimeImmutable('2025-12-31');
    $balance = $acc->getBalance($start, $end);

    expect($balance['start'])->toBe('2025-01-01');
    expect($balance['end'])->toBe('2025-12-31');
});
