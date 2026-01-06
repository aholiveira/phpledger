<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts\Domain\Reports;

use DateTimeImmutable;

/**
 * Interface for fetching category summary reports.
 *
 * Provides a method to retrieve summarized data for categories
 * within a given date range and period.
 *
 */
interface CategorySummaryInterface
{
    /**
     * Fetches category summary data for a given date range and period.
     *
     * @param DateTimeImmutable $from  Start date of the summary
     * @param DateTimeImmutable $to    End date of the summary
     * @param string            $period Period identifier (e.g., daily, monthly)
     *
     * @return array Summary data
     */
    public function fetch(DateTimeImmutable $from, DateTimeImmutable $to, string $period): array;
}
