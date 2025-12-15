<?php

namespace PHPLedger\Contracts\Domain\Reports;

use DateTimeImmutable;

interface CategorySummaryInterface
{
    public function fetch(DateTimeImmutable $from, DateTimeImmutable $to, string $period): array;
}
