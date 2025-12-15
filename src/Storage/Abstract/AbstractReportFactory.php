<?php

namespace PHPLedger\Storage\Abstract;

use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;
use UnexpectedValueException;

abstract class AbstractReportFactory
{
    protected static ?AbstractReportFactory $backendFactory = null;

    public static function init(string $backend = 'mysql'): void
    {
        if (static::$backendFactory !== null) {
            return;
        }

        if ($backend === 'mysql') {
            static::$backendFactory = new \PHPLedger\Storage\MySql\MySqlReportFactory();
            return;
        }

        throw new UnexpectedValueException('Report storage not implemented');
    }

    public static function categorySummary(): CategorySummaryInterface
    {
        return static::$backendFactory::categorySummary();
    }
}
