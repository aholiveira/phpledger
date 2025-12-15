<?php

namespace PHPLedger\Storage\MySql;

use PHPLedger\Reports\CategorySummaryReport;
use PHPLedger\Storage\Abstract\AbstractReportFactory;
use PHPLedger\Storage\MySql\Reports\MySqlCategorySummaryReport;

final class MySqlReportFactory extends AbstractReportFactory
{
    public static function categorySummary(): CategorySummaryReport
    {
        return new MySqlCategorySummaryReport();
    }
}
