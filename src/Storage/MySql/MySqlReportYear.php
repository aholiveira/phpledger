<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;

use DateTime;
use PHPLedger\Domain\ReportYear;

class MySqlReportYear extends ReportYear
{
    use MySqlReport {
        MySqlReport::__construct as private traitConstruct;
        MySqlReport::getReport as private traitGetReport;
    }
    protected int $firstYear;
    protected int $lastYear;
    public array $reportData;
    public array $columnHeaders;
    public array $savings;

    public function __construct()
    {
        $this->traitConstruct();
        $this->firstYear = 2999;
        $this->lastYear = 0;
    }
    public function getReport(array $params = []): ReportYear
    {
        $this->firstYear = \array_key_exists("first_year", $params) ? $params["first_year"] : date("Y") - 1;
        $this->lastYear = \array_key_exists("last_year", $params) ? $params["last_year"] : date("Y");
        if ($this->firstYear > $this->lastYear) {
            $temp = $this->lastYear;
            $this->lastYear = $this->firstYear;
            $this->firstYear = $temp;
        }
        for ($year = $this->firstYear; $year <= $this->lastYear; $year++) {
            $this->columnHeaders[$year] = $year;
            $this->dateFilters[$year]['start'] = date("Ymd", mktime(0, 0, 0, 1, 1, $year));
            $this->dateFilters[$year]['end'] = date("Ymd", mktime(0, 0, 0, 12, 31, $year));
        }
        $sql = "SELECT categoryId AS `row_header`, YEAR(entryDate) AS `col_header`, ROUND(SUM(ROUND(euroAmount,5)),2) AS `value`
            FROM movimentos
            WHERE YEAR(entryDate) BETWEEN ? AND ?
            GROUP BY categoryId, YEAR(entryDate)
            HAVING ROUND(SUM(ROUND(euroAmount,5)),2)<>0
            ORDER BY `row_header`, `col_header`";
        self::getData($sql, $this->firstYear, $this->lastYear);
        $this->traitGetReport($params);
        $account_type = MySqlObjectFactory::accounttype();
        $account = MySqlObjectFactory::account();
        $savings_types = $account_type->getList(['savings' => ['operator' => '=', 'value' => '1']]);
        $savings_accounts = [];
        foreach ($savings_types as $saving_type) {
            foreach ($account->getList(['typeId' => ['operator' => '=', 'value' => $saving_type->id]]) as $acc) {
                $savings_accounts[] = $acc;
            }
        }
        $this->savings = [];
        foreach (array_keys($this->columnHeaders) as $header) {
            $startDate = new DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $header)));
            $endDate = new DateTime(date("Y-m-d", mktime(0, 0, 0, 12, 31, $header)));
            foreach ($savings_accounts as $account) {
                $balances = $account->getBalance($startDate, $endDate);
                if (array_key_exists($header, $this->savings)) {
                    $this->savings[$header] += $balances["balance"];
                } else {
                    $this->savings[$header] = $balances["balance"];
                }
            }
        }
        return $this;
    }
    public function getFirstYear(): int
    {
        return $this->firstYear;
    }
    public function getLastYear(): int
    {
        return $this->lastYear;
    }
}
