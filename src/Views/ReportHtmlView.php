<?php

namespace PHPLedger\Views;

/**
 * View for generic report class
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

use PHPLedger\Domain\Report;
use PHPLedger\Util\NumberUtil;

class ReportHtmlView
{
    private array $periodIncome;
    private array $periodExpense;
    private array $periodTotal;
    protected report $report;
    public function __construct(report $report)
    {
        $this->report = $report;
    }
    public function printAsTable()
    {
        if (!isset($this->report->reportData)) {
            return "";
        }
        $lines = "<thead><tr><th colspan=3>Categoria</th>";
        foreach ($this->report->columnHeaders as $month) {
            $lines .= "<th>{$month}</th>\r\n";
        }
        $lines .= "<th>Average</th><th>Total</th></tr></thead>\r\n";
        $lines .= "<tbody>";
        $this->periodIncome = [];
        $this->periodExpense = [];
        $this->periodTotal = [];
        foreach ($this->report->reportData as $rowHeader => $dataRecord) {
            $lines .= $this->printRow($rowHeader, $dataRecord);
            if (array_key_exists('children', $dataRecord)) {
                foreach ($dataRecord['children'] as $childHeader => $childRecord) {
                    $lines .= $this->printRow($childHeader, $childRecord, $dataRecord);
                }
            }
        }
        $lines .= "</tbody>\r\n";
        $lines .= "<tfoot>\r\n";
        $lines .= $this->htmlArrayWithTitle($this->periodIncome, "Income", "income", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->periodExpense, "Expense", "expense", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->periodTotal, "Total", "totals", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->report->savings, "Savings", "savings", "saldos");
        $lines .= "</tfoot>\r\n";
        return $lines;
    }
    private function printRow($header, $record, $parent = null): string
    {
        $firstDate = $this->report->dateFilters[array_key_first($this->report->dateFilters)]['start'];
        $lastDate = $this->report->dateFilters[array_key_last($this->report->dateFilters)]['end'];

        $lines = $this->buildRowStart($record, $parent, $header, $firstDate, $lastDate);

        foreach (array_keys($this->report->columnHeaders) as $colHeader) {
            $value = $this->getRecordValue($record, $colHeader);
            $lines .= $this->buildColumnCell($record, $colHeader, $value);
            $this->updateTotals($colHeader, $value);
        }

        $lines .= $this->buildRowTotals($record);

        $lines .= "</tr>\r\n";

        return $lines;
    }

    private function buildRowStart($record, $parent, $header, $firstDate, $lastDate): string
    {
        if ($parent !== null) {
            return "<tr style='display: none;' class=\"group{$parent['id']}\">\r\n"
                . "<td></td><td></td><td class='subcategory-label' data-label='Sub-Categoria'>"
                . $this->buildRecordLink($record['id'], $header, $firstDate, $lastDate)
                . "</td>";
        }

        $row = "<tr class=\"group0\">\r\n";
        if (!empty($record['children'])) {
            $row .= $this->buildToggleIcons($record['id']);
        } else {
            $row .= "<td></td>\r\n";
        }

        $row .= "<td colspan=2 data-label='Categoria'>"
            . $this->buildRecordLink($record['id'], $header, $firstDate, $lastDate, true)
            . "</td>\r\n";

        return $row;
    }

    private function buildToggleIcons($id): string
    {
        return "<td><span class=\"open\" id=\"open{$id}\" onclick=\"toogleGroup('group{$id}');this.style.display = 'none';document.getElementById('close{$id}').style.removeProperty('display');\">&plus;</span>\r\n"
            . "<span class=\"close\" style=\"display: none;\" id=\"close{$id}\" onclick=\"toogleGroup('group{$id}');this.style.display = 'none';document.getElementById('open{$id}').style.removeProperty('display');\">&minus;</span>\r\n";
    }

    private function buildRecordLink($id, $label, $startDate, $endDate, $includeParent = false): string
    {
        $url = "ledger_entries.php?filter_sdate={$startDate}&amp;filter_edate={$endDate}&amp;filter_entry_type={$id}";
        if ($includeParent) {
            $url .= "&amp;filter_parentId={$id}";
        }
        return "<a href=\"{$url}\" title=\"Todos os movimentos da categoria" . ($includeParent ? " e sub-categorias" : "") . "\">{$label}</a>";
    }

    private function getRecordValue($record, $header): float
    {
        return (!empty($record['values']) && array_key_exists($header, $record['values']))
            ? $record['values'][$header]
            : 0;
    }

    private function buildColumnCell($record, $header, $value): string
    {
        $lines = "<td data-label='{$this->report->columnHeaders[$header]}' class=\"saldos\">\r\n";
        $lines .= "<span class='group{$record['id']}'>";

        if ($this->hasChildren($record)) {
            $sum = $record['subtotal'][$header] ?? 0;
            $lines .= $this->wrapValueLink($value + $sum, $record['id'], $header, true);
            $lines .= "<span style='display: none;' class='group{$record['id']}'>";
        }

        $lines .= $this->wrapValueLink($value, $record['id'], $header);

        $lines .= "</span>\r\n</td>\r\n";

        return $lines;
    }

    private function wrapValueLink($value, $recordId, $header, $includeParent = false): string
    {
        $startDate = $this->report->dateFilters[$header]['start'];
        $endDate = $this->report->dateFilters[$header]['end'];
        $normalized = NumberUtil::normalize($value);

        if ($value == 0) {
            return $normalized;
        }

        $url = "ledger_entries.php?filter_sdate={$startDate}&amp;filter_edate={$endDate}&amp;filter_entry_type={$recordId}";
        if ($includeParent) {
            $url .= "&amp;filter_parentId={$recordId}";
            $title = "Todos os movimentos da categoria e sub-categorias para este periodo";
        } else {
            $title = "Todos os movimentos da categoria para este periodo";
        }

        return "<a href=\"{$url}\" title=\"{$title}\">{$normalized}</a>";
    }

    private function updateTotals($header, $value): void
    {
        if (array_key_exists($header, $this->periodTotal)) {
            $this->periodTotal[$header] += $value;
        } else {
            $this->periodTotal[$header] = $value;
        }

        $this->addPeriodAmount($header, $value);
    }

    private function buildRowTotals($record): string
    {
        $data = $this->hasChildren($record) ? $record : $record['values'];
        return $this->htmlArrayAverageAndSum($data, "totals", "group{$record['id']}");
    }

    private function hasChildren(array $record): bool
    {
        return array_key_exists('children', $record) && sizeof($record['children']) > 0;
    }
    private function addPeriodAmount($period, $amount)
    {
        if ($amount >= 0) {
            if (array_key_exists($period, $this->periodIncome)) {
                $this->periodIncome[$period] += $amount;
            } else {
                $this->periodIncome[$period] = $amount;
            }
        } else {
            if (array_key_exists($period, $this->periodExpense)) {
                $this->periodExpense[$period] += $amount;
            } else {
                $this->periodExpense[$period] = $amount;
            }
        }
    }
    private function getArrayAverage($array)
    {
        $average = 0;
        $filtered_array = array_filter($array);
        if (count($filtered_array)) {
            $average = array_sum($filtered_array) / count($filtered_array);
        }
        return $average;
    }
    private function htmlArrayAverageAndSum($array, $tdclass, $spanclass = "")
    {
        $class = "";
        $retval = "";
        if (isset($tdclass)) {
            $class = "class=\"{$tdclass}\"";
        }
        if (array_key_exists('subtotal', $array)) {
            $average = $this->getArrayAverage($array['values']);
            $subaverage = $this->getArrayAverage($array['subtotal']);
            $retval .= "<td data-label='Average' {$class}><span style='display: none;' class='{$spanclass}'>" . NumberUtil::normalize($average) . "</span><span class='{$spanclass}'>" . NumberUtil::normalize($average + $subaverage) . "</span></td>\r\n";
            $retval .= "<td data-label='Total' {$class}><span style='display: none;' class='{$spanclass}'>" . NumberUtil::normalize(array_sum($array['values'])) . "</span><span class='{$spanclass}'>" . NumberUtil::normalize(array_sum($array['values']) + array_sum($array['subtotal'])) . "</span></td>\r\n";
        } else {
            $average = $this->getArrayAverage($array);
            $retval .= "<td data-label='Average' {$class}>" . NumberUtil::normalize($average) . "</td>\r\n";
            $retval .= "<td data-label='Total' {$class}>" . NumberUtil::normalize(array_sum($array)) . "</td>\r\n";
        }
        return $retval;
    }
    private function htmlMonthlyArray($array, $tdclass)
    {
        $class = "";
        $retval = "";
        if (isset($tdclass)) {
            $class = "class=\"{$tdclass}\"";
        }
        foreach ($this->report->columnHeaders as $key => $header) {
            $retval .= "<td data-label='{$header}' {$class}>" . NumberUtil::normalize(array_key_exists($key, $array) ? $array[$key] : 0) . "</td>\r\n";
        }
        return $retval;
    }
    private function htmlArrayWithTitle($array, $title, $trclass, $tdclass, $tdlabel = "")
    {
        if (!isset($array) || !isset($title)) {
            return "";
        }
        if (isset($trclass)) {
            $trclass = " class=\"{$trclass}\"";
        }
        $retval = "";
        $retval .= "<tr{$trclass}><td colspan=3 data-label='{$tdlabel}'>{$title}</td>\r\n";
        $retval .= $this->htmlMonthlyArray($array, $tdclass);
        $retval .= $this->htmlArrayAverageAndSum($array, "totals");
        $retval .= "</tr>\r\n";
        return $retval;
    }
}
