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
class report_year extends report implements ireport
{
    protected int $first_year;
    protected int $last_year;
    public array $reportData;
    public array $columnHeaders;
    public array $savings;

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        $this->first_year = 2999;
        $this->last_year = 0;
    }
    public function getReport(array $params = array()): report_year
    {
        global $object_factory;
        $this->first_year = array_key_exists("first_year", $params) ? $params["first_year"] : date("Y") - 1;
        $this->last_year = array_key_exists("last_year", $params) ? $params["last_year"] : date("Y");
        if ($this->first_year > $this->last_year) {
            $temp = $this->last_year;
            $this->last_year = $this->first_year;
            $this->first_year = $temp;
        }
        for ($year = $this->first_year; $year <= $this->last_year; $year++) {
            $this->columnHeaders[$year] = $year;
            $this->dateFilters[$year]['start'] = date("Ymd", mktime(0, 0, 0, 1, 1, $year));
            $this->dateFilters[$year]['end'] = date("Ymd", mktime(0, 0, 0, 12, 31, $year));
        }
        $sql = "SELECT tipo_mov AS `row_header`, YEAR(data_mov) AS `col_header`, ROUND(SUM(ROUND(valor_euro,5)),2) AS `value`
            FROM movimentos 
            WHERE YEAR(data_mov) BETWEEN ? AND ?
            GROUP BY tipo_mov, YEAR(data_mov) 
            HAVING ROUND(SUM(ROUND(valor_euro,5)),2)<>0 
            ORDER BY `row_header`, `col_header`";
        parent::getData($sql, $this->first_year, $this->last_year);
        parent::getReport($params);
        $account_type = $object_factory->accounttype();
        $account = $object_factory->account();
        $savings_types = $account_type->getAll(array('savings' => array('operator' => '=', 'value' => '1')));
        $savings_accounts = array();
        foreach ($savings_types as $saving_type) {
            foreach ($account->getAll(array('tipo_id' => array('operator' => '=', 'value' => $saving_type->id))) as $acc) {
                $savings_accounts[] = $acc;
            }
        }
        $this->savings = array();
        foreach (array_keys($this->columnHeaders) as $header) {
            $startDate = new \DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $header)));
            $endDate = new \DateTime(date("Y-m-d", mktime(0, 0, 0, 12, 31, $header)));
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
        return $this->first_year;
    }
    public function getLastYear(): int
    {
        return $this->last_year;
    }
}
