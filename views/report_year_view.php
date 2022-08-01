<?php

/**
 * View for report_year class
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class report_year_HtmlView extends report_HtmlView
{
    public function __construct(report_year $report)
    {
        parent::__construct($report);
    }
    public function printAsTable()
    {
        return parent::printAsTable();
    }
}
