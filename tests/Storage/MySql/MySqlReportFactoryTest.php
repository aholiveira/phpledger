<?php

namespace PHPLedgerTests\Unit\Storage;

use PHPLedger\Storage\ReportFactory;
use PHPLedger\Storage\MySql\MySqlReportFactory;
use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;
use UnexpectedValueException;

it('constructs with mysql backend', function () {
    $factory = new ReportFactory('mysql');

    $ref = new \ReflectionClass($factory);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);

    $backend = $prop->getValue($factory);

    expect($backend)->toBeInstanceOf(MySqlReportFactory::class);
});

it('throws exception for unsupported backend', function () {
    expect(fn () => new ReportFactory('unknown'))
        ->toThrow(UnexpectedValueException::class);
});

it('returns category summary from backend', function () {
    $factory = new ReportFactory('mysql');

    $summary = $factory->categorySummary();

    expect($summary)
        ->toBeInstanceOf(CategorySummaryInterface::class);
});
