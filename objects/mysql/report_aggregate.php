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
class report_month implements ireport
{
    public int $year;
    public array $monthNames;
    public array $expenseList;
    public array $expenseId;
    public array $savings;
    private mysqli $db;

    public function __construct(\mysqli $dblink)
    {
        $this->db = $dblink;
        for ($i = 1; $i <= 12; $i++) {
            $this->monthNames[$i] = date("M", mktime(0, 0, 0, $i, 1));
        }
    }

    public function getReport(array $params = array()): report_month
    {
        global $object_factory;
        $sql = "SELECT tipo_id AS category_id, tipo_desc, sum(euro_amount) AS sum, month(entry_date) AS `month`
                FROM movimentos INNER JOIN tipo_mov ON movimentos.category_id=tipo_id
                WHERE year(entry_date)=?
                GROUP BY category_id, month(entry_date)
                ORDER BY `month`, tipo_desc";
        $tipo_desc = "";
        $sum = 0;
        $month = 0;
        if (!array_key_exists("year", $params)) return $this;
        $this->year = $params["year"];
        if (!($this->db->ping())) {
            return $this;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $this->year);
        $stmt->execute();
        $stmt->bind_result($category_id, $tipo_desc, $sum, $month);
        while ($stmt->fetch()) {
            if ($sum <> 0) {
                $this->expenseList[$tipo_desc][$month] = $sum;
                $this->expenseId[$tipo_desc] = $category_id;
            }
            $month_list[$month] = $month;
        }
        $stmt->close();
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
        foreach ($month_list as $month) {
            $startDate = new \DateTime("{$this->year}-$month-1");
            $endDate = (new \DateTime("{$this->year}-$month-1"))->add(\DateInterval::createFromDateString('first day of next month'))->add(\DateInterval::createFromDateString("-1 day"));
            foreach ($savings_accounts as $account) {
                $balances = $account->getBalance($startDate, $endDate);
                if (array_key_exists($month, $this->savings)) {
                    $this->savings[$month] += $balances["balance"];
                } else {
                    $this->savings[$month] = $balances["balance"];
                }
            }
        }
        return $this;
    }

    /**
     * @return int Year of the report
     */
    public function getYear(): int
    {
        return $this->year;
    }
}
