<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Reports;

use DateTimeImmutable;
use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;

abstract class CategorySummaryReport implements CategorySummaryInterface
{
    abstract public function fetch(DateTimeImmutable $from, DateTimeImmutable $to, string $period): array;
}
