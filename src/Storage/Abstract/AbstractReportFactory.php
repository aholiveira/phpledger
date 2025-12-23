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
        switch ($backend) {
            case 'mysql':
                static::$backendFactory = new \PHPLedger\Storage\MySql\MySqlReportFactory();
                break;
            case '':
                break;
            default:
                throw new UnexpectedValueException('Report storage not implemented');
                break;
        }
    }

    public static function categorySummary(): CategorySummaryInterface
    {
        return static::$backendFactory::categorySummary();
    }
}
