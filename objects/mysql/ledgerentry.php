<?php

/**
 * Ledger entry class
 * Holds an object for a ledger entry on the ledger table
 * @property string $entry_date The entry's date. This should be in Y-m-d format
 * @property int $category_id
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
                if (strlen($where) > 0) {
                    $where .= " AND ";
                }
                $field_name = null === $table_name ? "`{$field}`" : "`{$table_name}`.`{$field}`";
                if (strtolower($filter['operator']) === "in") {
                    $where .= "{$field_name} {$filter['operator']} {$filter['value']}";
                } else {
                    $where .= "{$field_name} {$filter['operator']} '{$filter['value']}'";
                }
            }
        }
        if (strlen($where) > 0) {
            $where = "WHERE {$where}";
        }
        return $where;
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'mov_id' => 'id',
            'tipo_mov' => 'category_id',
            'data_mov' => 'entry_date',
            'conta_id' => 'account_id',
            'deb_cred' => 'direction',
            'moeda_mov' => 'currency_id',
            'valor_mov' => 'currency_amount',
            'valor_euro' => 'euro_amount',
            'cambio' => 'exchange_rate',
            'obs' => 'remarks',
            'last_modified' => 'updated_at'
        ];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL AUTO_INCREMENT",
            "entry_date" => "date DEFAULT NULL",
            "category_id" => "int(3) DEFAULT NULL",
            "account_id" => "int(3) DEFAULT NULL",
            "currency_id" => "char(3) NOT NULL DEFAULT 'EUR'",
            "direction" => "tinyint(1) NOT NULL DEFAULT 1",
            "currency_amount" => "float(10,2) DEFAULT NULL",
            "euro_amount" => "float(10,2) DEFAULT NULL",
            "exchange_rate" => "float(9,4) NOT NULL DEFAULT 1.0000",
            "a_pagar" => "tinyint(1) NOT NULL DEFAULT 0",
            "com_talao" => "tinyint(1) NOT NULL DEFAULT 0",
            "remarks" => "char(255) DEFAULT NULL",
            "username" => "char(255) DEFAULT ''",
            "created_at" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()",
            "updated_at" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()"
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    public static function getList(array $field_filter = []): array
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
        $retval = [];
        try {
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, [static::$_dblink])) {
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
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, [static::$_dblink]);
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
                WHERE entry_date<?" . (null !== $account_id ? " AND account_id=?" : "");
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            if (null === $account_id) {
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
        global $logger;
        $where = parent::getWhereFromArray($field_filter);
        $tableName = static::$tableName;
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euro_amount),euro_amount,0),5)),2) AS balance
                FROM {$tableName}
                WHERE {$where}";
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            $logger->dump($ex, "");
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
        if (!$this->validate())
            return $retval;
        try {
            $sql = "INSERT INTO {$this->tableName()}
            (id, entry_date, category_id, account_id, currency_id, direction, currency_amount, euro_amount, remarks, username, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)
            ON DUPLICATE KEY UPDATE
                entry_date=VALUES(entry_date),
                category_id=VALUES(category_id),
                account_id=VALUES(account_id),
                currency_id=VALUES(currency_id),
                direction=VALUES(direction),
                currency_amount=VALUES(currency_amount),
                euro_amount=VALUES(euro_amount),
                remarks=VALUES(remarks),
                username=VALUES(username),
                updated_at=NULL";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "isiisiddss",
                $this->id,
                $this->entry_date,
                $this->category_id,
                $this->account_id,
                $this->currency_id,
                $this->direction,
                $this->currency_amount,
                $this->euro_amount,
                $this->remarks,
                $this->username
            );
            $retval = $stmt->execute();
            $stmt->close();
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
