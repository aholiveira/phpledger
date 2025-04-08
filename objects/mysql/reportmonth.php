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
class report_month extends report implements ireport
{
    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        for ($i = 1; $i <= 12; $i++) {
            $this->columnHeaders[$i] = date("M", mktime(0, 0, 0, $i, 1));
        }
    }
    public function getReport(array $params = array()): report_month
    {
        global $object_factory;
        $year = array_key_exists("year", $params) ? $params["year"] : date("Y");
        for ($month = 1; $month <= 12; $month++) {
            $this->columnHeaders[$month] = date("M", mktime(0, 0, 0, $month, 1));
            $this->dateFilters[$month]['start'] = date("Ymd", mktime(0, 0, 0, $month, 1, $year));
            $this->dateFilters[$month]['end'] = date("Ymd", mktime(0, 0, 0, $month + 1, 0, $year));
        }
        $sql = "SELECT category_id as `row_header`, MONTH(entry_date) AS `col_header`, ROUND(SUM(ROUND(euro_amount,5)),2) AS `value`
            FROM movimentos
            WHERE YEAR(entry_date)=?
            GROUP BY category_id, MONTH(entry_date)
            HAVING ROUND(SUM(ROUND(euro_amount,5)),2)<>0
            ORDER BY row_header, col_header";
        parent::getData($sql, $year);
        parent::getReport($params);
        $account_type = $object_factory->accounttype();
        $account = $object_factory->account();
        $savings_types = $account_type->getList(array('savings' => array('operator' => '=', 'value' => '1')));
        $savings_accounts = array();
        foreach ($savings_types as $saving_type) {
            foreach ($account->getList(array('tipo_id' => array('operator' => '=', 'value' => $saving_type->id))) as $acc) {
                $savings_accounts[] = $acc;
            }
        }
        $this->savings = array();
        foreach (array_keys($this->columnHeaders) as $header) {
            $startDate = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $header, 1, $year)));
            $endDate = new \DateTime(date("Y-m-d", mktime(0, 0, 0, $header + 1, 0, $year)));
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
}
