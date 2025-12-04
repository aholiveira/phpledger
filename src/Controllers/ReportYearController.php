<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\ReportYearView;
use PHPLedger\Views\ViewFactory;

final class ReportYearController
{
    public function handle(): void
    {
        $filterArray = [
            "firstYear" => FILTER_VALIDATE_INT,
            "lastYear" => FILTER_VALIDATE_INT,
            "action" => FILTER_DEFAULT
        ];
        $filtered = filter_input_array(INPUT_GET, $filterArray, true);
        $firstYear = $this->validateYear(empty($filtered["firstYear"]) ?  "" : $filtered["firstYear"], date("Y") - 1);
        $lastYear = $this->validateYear(empty($filtered["lastYear"]) ?  "" : $filtered["lastYear"], date("Y"));
        $report = ObjectFactory::reportYear();
        $reportHtml = ViewFactory::instance()->reportYearHtmlView($report);
        $report->getReport(["first_year" => $firstYear, "last_year" => $lastYear]);
        $view = new ReportYearView;
        $view->render("report_year", $firstYear, $lastYear, $reportHtml);
    }
    private function validateYear(string $value, int $default): int
    {
        if (!is_numeric($value) || ($value <= 1990 && $value >= 2100)) {
            return $default;
        } else {
            return (int)$value;
        }
    }
}
