<?php

/**
 * Month Report
 * Class to generate a month report with a summary of income and expense per month
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use \PHPLedger\Domain\ReportMonth;
class MySqlReportMonth extends ReportMonth
{
    use MySqlReport {
        MySqlReport::__construct as private traitConstruct;
        MySqlReport::getReport as private traitGetReport;
    }
    public int $year;
    //public array $savings = [];
    public function __construct()
    {
        $this->traitConstruct();
        $this->year = (int) date("Y");
        $this->initColumnHeaders($this->year);
    }
    private function initColumnHeaders(int $year): void
    {
        for ($month = 1; $month <= 12; $month++) {
            $this->columnHeaders[$month] = date("M", mktime(0, 0, 0, $month, 1));
            $this->dateFilters[$month] = [
                'start' => date("Ymd", mktime(0, 0, 0, $month, 1, $year)),
                'end' => date("Ymd", mktime(0, 0, 0, $month + 1, 0, $year))
            ];
        }
    }

    public function getReport(array $params = []): self
    {
        $this->year = array_key_exists("year", $params) ? $params["year"] : date("Y");
        $this->initColumnHeaders($this->year);
        $sql = "SELECT categoryId as `row_header`, MONTH(entry_date) AS `col_header`, ROUND(SUM(ROUND(euroAmount,5)),2) AS `value`
            FROM movimentos
            WHERE YEAR(entry_date)=?
            GROUP BY categoryId, MONTH(entry_date)
            HAVING ROUND(SUM(ROUND(euroAmount,5)),2)<>0
            ORDER BY row_header, col_header";
        self::getData($sql, $this->year);
        $this->traitGetReport($params);
        $account_type = MySqlObjectFactory::accounttype();
        $account = MySqlObjectFactory::account();
        $savings_types = $account_type->getList(['savings' => ['operator' => '=', 'value' => '1']]);
        $savings_accounts = [];
        foreach ($savings_types as $saving_type) {
            foreach ($account->getList(['tipo_id' => ['operator' => '=', 'value' => $saving_type->id]]) as $acc) {
                $savings_accounts[] = $acc;
            }
        }
        $this->savings = [];
        foreach (array_keys($this->columnHeaders) as $header) {
            $startDate = new \DateTimeImmutable("{$this->year}-$header-01");
            $endDate = $startDate->modify('last day of this month');
            foreach ($savings_accounts as $account) {
                $balances = $account->getBalance($startDate, $endDate);
                $this->savings[$header] = ($this->savings[$header] ?? 0) + $balances["balance"];
            }
        }
        return $this;
    }
}
