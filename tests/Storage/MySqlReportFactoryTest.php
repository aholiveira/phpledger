<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Storage;

use PHPLedger\Storage\ReportFactory;
use PHPLedger\Storage\Abstract\AbstractReportFactory;
use PHPLedger\Storage\MySql\MySqlReportFactory;
use UnexpectedValueException;

beforeEach(function () {
    $ref = new \ReflectionClass(AbstractReportFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $prop->setValue(null, null);
});

it('constructs with mysql backend', function () {
    $factory = new ReportFactory('mysql');
    $ref = new \ReflectionClass(AbstractReportFactory::class);
    $prop = $ref->getProperty('backendFactory');
    $prop->setAccessible(true);
    $backend = $prop->getValue();
    expect($backend)->toBeInstanceOf(MySqlReportFactory::class);
});

it('throws exception for unsupported backend', function () {
    expect(fn() => new ReportFactory('unknown'))->toThrow(UnexpectedValueException::class);
});

it('returns category summary from backend', function () {
    $factory = new ReportFactory('mysql');
    $summary = AbstractReportFactory::categorySummary();
    expect($summary)->toBeInstanceOf(\PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface::class);
});
