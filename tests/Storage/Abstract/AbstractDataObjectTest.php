<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Storage\Abstract;

use PHPLedger\Storage\Abstract\AbstractDataObject;
use PHPLedger\Contracts\DataObjectInterface;

class TestDataObject extends AbstractDataObject
{
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function validate(): bool
    {
        return true;
    }
    public function errorMessage(): string
    {
        return "error";
    }
    public function create(): self
    {
        return $this;
    }
    public function read(int $id): ?self
    {
        return new self($id);
    }
    public function update(): bool
    {
        return true;
    }
    public function delete(): bool
    {
        return true;
    }
    public static function getNextId(): int
    {
        return 1;
    }
    public static function getList(array $fieldFilter = []): array
    {
        return [];
    }
    public static function getById(int $id): ?DataObjectInterface
    {
        return new self($id);
    }
}

it('can instantiate a concrete subclass', function () {
    $obj = new TestDataObject(5);
    expect($obj)->toBeInstanceOf(AbstractDataObject::class);
    expect($obj->id)->toBe(5);
});

it('returns validation and error messages', function () {
    $obj = new TestDataObject();
    expect($obj->validate())->toBeTrue();
    expect($obj->errorMessage())->toBe("error");
});

it('can update and delete', function () {
    $obj = new TestDataObject();
    expect($obj->update())->toBeTrue();
    expect($obj->delete())->toBeTrue();
});

it('provides next id and lists', function () {
    expect(TestDataObject::getNextId())->toBe(1);
    expect(TestDataObject::getList())->toBe([]);
    $obj = TestDataObject::getById(10);
    expect($obj)->toBeInstanceOf(AbstractDataObject::class);
    expect($obj->id)->toBe(10);
});

it('can get ID via abstract method', function () {
    $obj = new TestDataObject(42);
    expect($obj->getId())->toBe(42);
});
