<?php

/**
 * View for ReportMonth class
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Domain\ReportMonth;
class ReportMonthHtmlView extends reportHtmlView
{
    public function __construct(ReportMonth $report)
    {
        parent::__construct($report);
    }
    public function printAsTable()
    {
        return parent::printAsTable();
    }
}
