<?php

use PHPLedger\Storage\ReportFactory;
use PHPLedger\Storage\MySql\Reports\MySqlCategorySummaryReport;
use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;

it('creates mysql backend by default', function () {
    $factory = new ReportFactory();
    $report = $factory->categorySummary();

    expect($report)
        ->toBeInstanceOf(CategorySummaryInterface::class)
        ->toBeInstanceOf(MySqlCategorySummaryReport::class);
});

it('creates mysql backend explicitly', function () {
    $factory = new ReportFactory('mysql');
    expect($factory->categorySummary())
        ->toBeInstanceOf(CategorySummaryInterface::class);
});

it('throws exception for unsupported backend', function () {
    new ReportFactory('sqlite');
})->throws(UnexpectedValueException::class);
