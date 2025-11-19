<?php

/**
 * View for generic report class
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Domain\report;
class ReportHtmlView
{
    private $_periodIncome;
    private $_periodExpense;
    private $_periodTotal;
    protected report $_report;
    public function __construct(report $report)
    {
        $this->_report = $report;
    }
    public function printAsTable()
    {
        if (!isset($this->_report->reportData)) {
            return "";
        }
        $lines = "<thead><tr><th colspan=3>Categoria</th>";
        foreach ($this->_report->columnHeaders as $month) {
            $lines .= "<th>{$month}</th>\r\n";
        }
        $lines .= "<th>Average</th><th>Total</th></tr></thead>\r\n";
        $lines .= "<tbody>";
        $this->_periodIncome = [];
        $this->_periodExpense = [];
        $this->_periodTotal = [];
        foreach ($this->_report->reportData as $rowHeader => $dataRecord) {
            $lines .= $this->print_row($rowHeader, $dataRecord);
            if (array_key_exists('children', $dataRecord)) {
                foreach ($dataRecord['children'] as $childHeader => $childRecord) {
                    $lines .= $this->print_row($childHeader, $childRecord, $dataRecord);
                }
            }
        }
        $lines .= "</tbody>\r\n";
        $lines .= "<tfoot>\r\n";
        $lines .= $this->htmlArrayWithTitle($this->_periodIncome, "Income", "income", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->_periodExpense, "Expense", "expense", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->_periodTotal, "Total", "totals", "saldos");
        $lines .= $this->htmlArrayWithTitle($this->_report->savings, "Savings", "savings", "saldos");
        $lines .= "</tfoot>\r\n";
        return $lines;
    }
    private function print_row($header, $record, $parent = null): string
    {
        $lines = "";
        $first_date = $this->_report->dateFilters[array_key_first($this->_report->dateFilters)]['start'];
        $last_date = $this->_report->dateFilters[array_key_last($this->_report->dateFilters)]['end'];
        if (null !== $parent) {
            $lines .= "<tr style='display: none;' class=\"group{$parent['id']}\">\r\n";
            $lines .= "<td></td><td></td><td class='subcategory-label' data-label='Sub-Categoria'><a href=\"ledger_entries.php?filter_sdate={$first_date}&amp;filter_edate={$last_date}&amp;filter_entry_type={$record['id']}\" title=\"Todos os movimentos da categoria\">{$header}</a></td>";
        } else {
            $lines .= "<tr class=\"group0\">\r\n";
            if (array_key_exists('children', $record) && sizeof($record['children']) > 0) {
                $lines .= "<td><span class=\"open\" id=\"open{$record['id']}\" onclick=\"toogleGroup('group{$record['id']}');this.style.display = 'none';document.getElementById('close{$record['id']}').style.removeProperty('display');\">&plus;</span>\r\n"
                    . "<span class=\"close\" style=\"display: none;\" id=\"close{$record['id']}\" onclick=\"toogleGroup('group{$record['id']}');this.style.display = 'none';document.getElementById('open{$record['id']}').style.removeProperty('display');\">&minus;</span>\r\n";
            } else {
                $lines .= "<td></td>\r\n";
            }
            $lines .= "<td colspan=2 data-label='Categoria'><a href=\"ledger_entries.php?filter_sdate={$first_date}&amp;filter_edate={$last_date}&amp;filter_entry_type={$record['id']}&amp;filter_parent_id={$record['id']}\" title=\"Todos os movimentos da categoria e sub-categorias\">{$header}</a></td>\r\n";
        }
        foreach (array_keys($this->_report->columnHeaders) as $header) {
            $value = (sizeof($record['values']) > 0 && array_key_exists($header, $record['values'])) ? $record['values'][$header] : 0;
            $sum = 0;
            $lines .= "<td data-label='{$this->_report->columnHeaders[$header]}' class=\"saldos\">\r\n";
            $lines .= "<span class='group{$record['id']}'>";
            if ($this->hasChildren($record)) {
                $sum = array_key_exists($header, $record['subtotal']) ? $record['subtotal'][$header] : 0;
                $lines .= (($value + $sum) <> 0 ? "<a href=\"ledger_entries.php?filter_sdate={$this->_report->dateFilters[$header]['start']}&amp;filter_edate={$this->_report->dateFilters[$header]['end']}&amp;filter_entry_type={$record['id']}&amp;filter_parent_id={$record['id']}\" title=\"Todos os movimentos da categoria e sub-categorias para este periodo\">" : "");
                $lines .= normalizeNumber($value + $sum);
                $lines .= (($value + $sum) <> 0 ? "</a>" : "") . "</span>\r\n";
                $lines .= "<span style='display: none;' class='group{$record['id']}'>";
            }
            $lines .= $value <> 0 ? "<a href=\"ledger_entries.php?filter_sdate={$this->_report->dateFilters[$header]['start']}&amp;filter_edate={$this->_report->dateFilters[$header]['end']}&amp;filter_entry_type={$record['id']}\" title=\"Todos os movimentos da categoria para este periodo\">" : "";
            $lines .= normalizeNumber($value);
            $lines .= ($value <> 0 ? "</a>" : "") . "</span>\r\n";
            $lines .= "</td>\r\n";
            if (array_key_exists($header, $this->_periodTotal)) {
                $this->_periodTotal[$header] += $value;
            } else {
                $this->_periodTotal[$header] = $value;
            }
            $this->addPeriodAmount($header, $value);
        }
        if ($this->hasChildren($record)) {
            $lines .= $this->htmlArrayAverageAndSum($record, "totals", "group{$record['id']}");
        } else {
            $lines .= $this->htmlArrayAverageAndSum($record['values'], "totals", "group{$record['id']}");
        }
        $lines .= "</tr>\r\n";
        return $lines;
    }
    private function hasChildren(array $record): bool
    {
        return array_key_exists('children', $record) && sizeof($record['children']) > 0;
    }
    private function addPeriodAmount($period, $amount)
    {
        if ($amount >= 0) {
            if (array_key_exists($period, $this->_periodIncome)) {
                $this->_periodIncome[$period] += $amount;
            } else {
                $this->_periodIncome[$period] = $amount;
            }
        } else {
            if (array_key_exists($period, $this->_periodExpense)) {
                $this->_periodExpense[$period] += $amount;
            } else {
                $this->_periodExpense[$period] = $amount;
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
            $retval .= "<td data-label='Average' {$class}><span style='display: none;' class='{$spanclass}'>" . normalizeNumber($average) . "</span><span class='{$spanclass}'>" . normalizeNumber($average + $subaverage) . "</span></td>\r\n";
            $retval .= "<td data-label='Total' {$class}><span style='display: none;' class='{$spanclass}'>" . normalizeNumber(array_sum($array['values'])) . "</span><span class='{$spanclass}'>" . normalizeNumber(array_sum($array['values']) + array_sum($array['subtotal'])) . "</span></td>\r\n";
        } else {
            $average = $this->getArrayAverage($array);
            $retval .= "<td data-label='Average' {$class}>" . normalizeNumber($average) . "</td>\r\n";
            $retval .= "<td data-label='Total' {$class}>" . normalizeNumber(array_sum($array)) . "</td>\r\n";
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
        foreach ($this->_report->columnHeaders as $key => $header) {
            $retval .= "<td data-label='{$header}' {$class}>" . normalizeNumber(array_key_exists($key, $array) ? $array[$key] : 0) . "</td>\r\n";
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
