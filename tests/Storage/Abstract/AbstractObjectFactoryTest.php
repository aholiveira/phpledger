<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Storage\Abstract;

use PHPLedger\Storage\Abstract\AbstractObjectFactory;
use PHPLedger\Storage\MySql\MySqlObjectFactory;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\DataStorageInterface;
use PHPLedger\Domain\Account;
use PHPLedger\Domain\AccountType;
use PHPLedger\Domain\Currency;
use PHPLedger\Domain\Defaults;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Domain\Ledger;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Domain\User;
use UnexpectedValueException;

beforeEach(function () {
    $ref = new \ReflectionClass(AbstractObjectFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $prop->setValue(null, null);
});

it('initializes with mysql backend', function () {
    AbstractObjectFactory::init('mysql');
    $ref = new \ReflectionClass(AbstractObjectFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $backend = $prop->getValue();
    expect($backend)->toBeInstanceOf(MySqlObjectFactory::class);
});

it('does not reinitialize if already set', function () {
    AbstractObjectFactory::init('mysql');
    $ref = new \ReflectionClass(AbstractObjectFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $firstInstance = $prop->getValue();

    AbstractObjectFactory::init('mysql');
    $secondInstance = $prop->getValue();
    expect($secondInstance)->toBe($firstInstance);
});

it('throws exception for unsupported backend', function () {
    expect(fn() => AbstractObjectFactory::init('unknown'))->toThrow(UnexpectedValueException::class);
});

it('delegates dataStorage to backend', function () {
    AbstractObjectFactory::init('mysql');
    $storage = AbstractObjectFactory::dataStorage();
    expect($storage)->toBeInstanceOf(DataStorageInterface::class);
});

it('delegates account related methods to backend', function () {
    AbstractObjectFactory::init('mysql');
    expect(AbstractObjectFactory::account())->toBeInstanceOf(Account::class);
    expect(AbstractObjectFactory::accountType())->toBeInstanceOf(AccountType::class);
    expect(AbstractObjectFactory::currency())->toBeInstanceOf(Currency::class);
    expect(AbstractObjectFactory::defaults())->toBeInstanceOf(Defaults::class);
    expect(AbstractObjectFactory::entryCategory())->toBeInstanceOf(EntryCategory::class);
    expect(AbstractObjectFactory::ledger())->toBeInstanceOf(Ledger::class);
    expect(AbstractObjectFactory::ledgerEntry())->toBeInstanceOf(LedgerEntry::class);
    expect(AbstractObjectFactory::user())->toBeInstanceOf(User::class);
});
