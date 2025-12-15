<?php

namespace PHPLedger\Storage;

use PHPLedger\Storage\Abstract\AbstractReportFactory;

final class ReportFactory extends AbstractReportFactory
{
    public function __construct(string $backend)
    {
        parent::init($backend);
    }
}
