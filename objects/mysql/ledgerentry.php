<?php

/**
 * Ledger entry class
 * Holds an object for a ledger entry on the ledger table
 * @property int id Internal object identifier
 * @property string entry_date The entry's date. This should be in Y-m-d format
 * @property int category_id
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class ledgerentry extends mysql_object implements iobject
{
    public string $entry_date;
    public int $category_id;
    public entry_category $category;
    public float $currency_amount;
    public string $currency_id;
    public currency $currency;
    public float $euro_amount;
    public float $exchange_rate;
    public int $account_id;
    public ?account $account;
    public int $direction;
    public ?string $remarks;
    public string $username = "";
    public string $created_at;
    public string $updated_at;
    public int $ledger_id;
    protected static string $tableName = "movimentos";

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
    }
    protected static function getWhereFromArray(array $field_filter, ?string $table_name = null): string
    {
        $where = "";
        foreach ($field_filter as $filter_entry) {
            foreach ($filter_entry as $field => $filter) {
                if (strlen($where) > 0) $where .= " AND ";
                $field_name = is_null($table_name) ? "`{$field}`" : "`{$table_name}`.`{$field}`";
                $where .= "{$field_name} {$filter['operator']} '{$filter['value']}'";
            }
        }
        if (strlen($where) > 0) $where = "WHERE {$where}";
        return $where;
    }
    public static function getList(array $field_filter = array()): array
    {
        $where = self::getWhereFromArray($field_filter);
        $sql = "SELECT id, entry_date, category_id,
            account_id,
            round(currency_amount,2) as currency_amount, `direction`, currency_id,
            exchange_rate, euro_amount AS euro_amount,
            remarks, username, created_at, updated_at
            FROM " . static::tableName() . "
            {$where}
            ORDER BY entry_date, id";
        $retval = array();
        try {
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $newobject->getValuesForForeignFields();
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById($id): ?ledgerentry
    {
        $sql = "SELECT id, entry_date, category_id,
            account_id,
            round(currency_amount,2) as currency_amount, `direction`, currency_id,
            exchange_rate, euro_amount,
            remarks, username, created_at, updated_at
            FROM " . static::tableName() . "
            WHERE id=?";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
            if ($retval instanceof ledgerentry) {
                $retval->getValuesForForeignFields();
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function getBalanceBeforeDate($date, $account_id = null): ?float
    {
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euro_amount),euro_amount,0),5)),2) AS balance
                FROM {$this->tableName()}
                WHERE entry_date<?" . (!is_null($account_id) ? " AND account_id=?" : "");
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            if (is_null($account_id)) {
                $stmt->bind_param("s", $date);
            } else {
                $stmt->bind_param("si", $date, $account_id);
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    /**
     * @param array $field_filter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     */

    public static function getBalanceForFilter(array $field_filter)
    {
        $where = parent::getWhereFromArray($field_filter);
        $tableName = static::$tableName;
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euro_amount),euro_amount,0),5)),2) AS balance
                FROM {$tableName}
                WHERE {$where}";
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            print_var($ex, "", true);
        }
        return $retval;
    }
    private function getValuesForForeignFields()
    {
        if (isset($this->category_id)) {
            $this->category = entry_category::getById($this->category_id);
        }
        if (isset($this->account_id)) {
            $this->account = account::getById($this->account_id);
        }
        if (isset($this->currency_id)) {
            $this->currency = currency::getById($this->currency_id);
        }
    }
    public function update(): bool
    {
        $retval = false;
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            static::$_dblink->begin_transaction();
            if (empty($this->id)) {
                $this->id = $this->getNextId();
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET
                    entry_date =?,
                    category_id =?,
                    account_id =?,
                    currency_id =?,
                    direction =?,
                    currency_amount =?,
                    euro_amount =?,
                    remarks =?,
                    username=?,
                    updated_at=NULL
                    WHERE id =?";
            } else {
                $sql = "INSERT INTO {$this->tableName()}
                        (entry_date, category_id, account_id, currency_id, direction, currency_amount, euro_amount, remarks, username, id, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param(
                "ssssssssss",
                $this->entry_date,
                $this->category_id,
                $this->account_id,
                $this->currency_id,
                $this->direction,
                $this->currency_amount,
                $this->euro_amount,
                $this->remarks,
                $this->username,
                $this->id
            );
            $retval = $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getNextId(string $field = "id"): int
    {
        return parent::getNextId($field);
    }
    public function delete(): bool
    {
        return false;
    }
}
