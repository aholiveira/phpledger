<?php
/**
 * Year Report
 * Class to generate a year report with a summary of income and expense per month
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Domain;
use PHPLedger\Contracts\ReportInterface;
use PHPLedger\Storage\ObjectFactory;
abstract class Report implements ReportInterface
{
    public array $reportData = [];
    public array $columnHeaders;
    public array $dateFilters;
    public array $savings;

    abstract public function getReport(array $params = []): self;
    protected function updateSubtotal($category, array $childValues): void
    {
        foreach ($childValues as $col_header => $value) {
            $this->reportData[$category->description]['id'] ??= $category->id;
            $this->reportData[$category->description]['values'] ??= [];
            $this->reportData[$category->description]['subtotal'][$col_header] ??= 0;
            $this->reportData[$category->description]['subtotal'][$col_header] += $value;
        }
    }
}
