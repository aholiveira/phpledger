<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Domain\AccountType;
use PHPLedger\Contracts\DataObjectInterface;

class AccountTypeDummy extends AccountType
{
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

it('initializes id to zero', function () {
    $obj = new AccountTypeDummy();
    expect($obj->id)->toBe(0);
});

it('implements DataObjectInterface', function () {
    $obj = new AccountTypeDummy();
    expect($obj)->toBeInstanceOf(DataObjectInterface::class);
});

it('initializes description to null', function () {
    $obj = new AccountTypeDummy();
    expect($obj->description)->toBeNull();
});

it('initializes savings to zero', function () {
    $obj = new AccountTypeDummy();
    expect($obj->savings)->toBe(0);
});

it('allows changing properties', function () {
    $obj = new AccountTypeDummy();
    $obj->description = 'Test';
    $obj->savings = 50;
    expect($obj->description)->toBe('Test');
    expect($obj->savings)->toBe(50);
});
