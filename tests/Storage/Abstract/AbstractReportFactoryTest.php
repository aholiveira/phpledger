<?php

namespace PHPLedgerTests\Unit\Storage\Abstract;

use PHPLedger\Storage\Abstract\AbstractReportFactory;
use PHPLedger\Storage\MySql\MySqlReportFactory;
use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;
use UnexpectedValueException;
use PHPUnit\Framework\TestCase;

beforeEach(function () {
    $ref = new \ReflectionClass(AbstractReportFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $prop->setValue(null, null);
});

it('initializes with mysql backend', function () {
    AbstractReportFactory::init('mysql');
    $ref = new \ReflectionClass(AbstractReportFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $backend = $prop->getValue();
    expect($backend)->toBeInstanceOf(MySqlReportFactory::class);
});

it('does not reinitialize if already set', function () {
    AbstractReportFactory::init('mysql');
    $ref = new \ReflectionClass(AbstractReportFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $firstInstance = $prop->getValue();

    AbstractReportFactory::init('mysql');
    $secondInstance = $prop->getValue();
    expect($secondInstance)->toBe($firstInstance);
});

it('throws exception for unsupported backend', function () {
    expect(fn() => AbstractReportFactory::init('unknown'))->toThrow(UnexpectedValueException::class);
});

it('categorySummary calls backend method', function () {
    AbstractReportFactory::init('mysql');
    $summary = AbstractReportFactory::categorySummary();
    expect($summary)->toBeInstanceOf(CategorySummaryInterface::class);
});
