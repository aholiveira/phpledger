<?php

namespace PHPLedger\Reports;

use DateTimeImmutable;
use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;

abstract class CategorySummaryReport implements CategorySummaryInterface
{
    abstract public function fetch(DateTimeImmutable $from, DateTimeImmutable $to, string $period): array;
}
