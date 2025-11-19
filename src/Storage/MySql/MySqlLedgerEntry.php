<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Util\Logger;
class MySqlLedgerEntry extends LedgerEntry
{
    use MySqlObject;
    protected static string $tableName = "movimentos";

    protected static function getWhereFromArray(array $fieldFilter, ?string $table_name = null): string
    {
        $conditions = [];

        foreach ($fieldFilter as $filter_entry) {
            foreach ($filter_entry as $field => $filter) {
                $field_name = $table_name !== null ? "`{$table_name}`.`{$field}`" : "`{$field}`";
                $operator = strtolower($filter['operator']);
                $value = $filter['value'];

                if ($operator === 'in' && \is_array($value)) {
                    $escaped_values = array_map(fn($v) => "'" . addslashes((string) $v) . "'", $value);
                    $conditions[] = "{$field_name} IN (" . implode(', ', $escaped_values) . ")";
                } else {
                    $escaped_value = addslashes((string) $value);
                    $conditions[] = "{$field_name} {$filter['operator']} '{$escaped_value}'";
                }
            }
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'mov_id' => 'id',
            'tipo_mov' => 'categoryId',
            'data_mov' => 'entry_date',
            'conta_id' => 'accountId',
            'deb_cred' => 'direction',
            'moeda_mov' => 'currency_id',
            'valor_mov' => 'currencyAmount',
            'valor_euro' => 'euroAmount',
            'cambio' => 'exchangeRate',
            'obs' => 'remarks',
            'last_modified' => 'updatedAt',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'exchange_rate' => 'exchangeRate',
            'euro_amount' => 'euroAmount',
            'currency_amount' => 'currencyAmount',
            'category_id' => 'categoryId',
            'account_id' => 'accountId'
        ];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL AUTO_INCREMENT",
            "entry_date" => "date DEFAULT NULL",
            "categoryId" => "int(3) DEFAULT NULL",
            "accountId" => "int(3) DEFAULT NULL",
            "currency_id" => "char(3) NOT NULL DEFAULT 'EUR'",
            "direction" => "tinyint(1) NOT NULL DEFAULT 1",
            "currencyAmount" => "float(10,2) DEFAULT NULL",
            "euroAmount" => "float(10,2) DEFAULT NULL",
            "exchangeRate" => "float(9,4) NOT NULL DEFAULT 1.0000",
            "a_pagar" => "tinyint(1) NOT NULL DEFAULT 0",
            "com_talao" => "tinyint(1) NOT NULL DEFAULT 0",
            "remarks" => "char(255) DEFAULT NULL",
            "username" => "char(255) DEFAULT ''",
            "createdAt" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()",
            "updatedAt" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()"
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = "SELECT id, entry_date, categoryId,
            accountId,
            round(currencyAmount,2) as currencyAmount, `direction`, currency_id,
            exchangeRate, euroAmount,
            remarks, username, createdAt, updatedAt
            FROM " . static::tableName() . "
            {$where}
            ORDER BY entry_date, id";
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__)) {
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
        $sql = "SELECT id, entry_date, categoryId,
            accountId,
            round(currencyAmount,2) as currencyAmount, `direction`, currency_id,
            exchangeRate, euroAmount,
            remarks, username, createdAt, updatedAt
            FROM " . static::tableName() . "
            WHERE id=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
            if ($retval instanceof MySqlLedgerEntry) {
                $retval->getValuesForForeignFields();
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function getBalanceBeforeDate($date, $accountId = null): ?float
    {
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euroAmount),euroAmount,0),5)),2) AS balance
                FROM {$this->tableName()}
                WHERE entry_date<?" . (null !== $accountId ? " AND accountId=?" : "");
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            if (null === $accountId) {
                $stmt->bind_param("s", $date);
            } else {
                $stmt->bind_param("si", $date, $accountId);
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
     * @param array $fieldFilter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     */

    public static function getBalanceForFilter(array $fieldFilter)
    {
        $where = self::getWhereFromArray($fieldFilter);
        $tableName = static::$tableName;
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euroAmount),euroAmount,0),5)),2) AS balance
                FROM {$tableName}
                WHERE {$where}";
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            Logger::instance()->dump($ex, "");
        }
        return $retval;
    }
    protected function getValuesForForeignFields()
    {
        if (isset($this->categoryId)) {
            $this->category = MySqlEntryCategory::getById($this->categoryId);
        }
        if (isset($this->accountId)) {
            $this->account = MySqlAccount::getById($this->accountId);
        }
        if (isset($this->currency_id)) {
            $this->currency = MySqlCurrency::getById($this->currency_id);
        }
    }
    public function update(): bool
    {
        $retval = false;
        if (!$this->validate()) {
            return $retval;
        }
        try {
            $sql = "INSERT INTO {$this->tableName()}
            (id, entry_date, categoryId, accountId, currency_id, direction, currencyAmount, euroAmount, remarks, username, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)
            ON DUPLICATE KEY UPDATE
                entry_date=VALUES(entry_date),
                categoryId=VALUES(categoryId),
                accountId=VALUES(accountId),
                currency_id=VALUES(currency_id),
                direction=VALUES(direction),
                currencyAmount=VALUES(currencyAmount),
                euroAmount=VALUES(euroAmount),
                remarks=VALUES(remarks),
                username=VALUES(username),
                updatedAt=NULL";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "isiisiddss",
                $this->id,
                $this->entry_date,
                $this->categoryId,
                $this->accountId,
                $this->currency_id,
                $this->direction,
                $this->currencyAmount,
                $this->euroAmount,
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
    public function delete(): bool
    {
        return false;
    }
}
