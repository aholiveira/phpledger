<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

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
