<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage;

use PHPLedger\Contracts\Domain\Reports\CategorySummaryInterface;
use PHPLedger\Storage\Abstract\AbstractReportFactory;
use PHPLedger\Storage\MySql\MySqlReportFactory;
use UnexpectedValueException;

final class ReportFactory extends AbstractReportFactory
{
    protected AbstractReportFactory $backendFactory;
    public function __construct(string $backend = 'mysql')
    {
        $this->backendFactory = match ($backend) {
            'mysql' => new MySqlReportFactory(),
            default => throw new UnexpectedValueException('Report storage not implemented'),
        };
    }

    public function categorySummary(): CategorySummaryInterface
    {
        return $this->backendFactory->categorySummary();
    }
}
