<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\Abstract;

use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;

abstract class AbstractReportFactory
{
    abstract protected function categorySummary(): CategorySummaryInterface;
}
