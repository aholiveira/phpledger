<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use Exception;
use mysqli_sql_exception;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Services\Logger;

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
    private static function getSelect(): string
    {
        return "SELECT id, entryDate, categoryId,
            accountId,
            round(currencyAmount,2) as currencyAmount, `direction`, currencyId,
            exchangeRate, euroAmount,
            remarks, username, createdAt, updatedAt
            FROM " . static::tableName();
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'mov_id' => 'id',
            'tipo_mov' => 'categoryId',
            'data_mov' => 'entryDate',
            'conta_id' => 'accountId',
            'deb_cred' => 'direction',
            'moeda_mov' => 'currencyId',
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
            'account_id' => 'accountId',
            'currency_id' => 'currencyId',
            'entry_date' => 'entryDate'
        ];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL AUTO_INCREMENT",
            "entryDate" => "date DEFAULT NULL",
            "categoryId" => "int(3) DEFAULT NULL",
            "accountId" => "int(3) DEFAULT NULL",
            "currencyId" => "char(3) NOT NULL DEFAULT 'EUR'",
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
        $sql = self::getSelect() . " {$where} ORDER BY entryDate, id";
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__)) {
                $newobject->getValuesForForeignFields();
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById($id): ?self
    {
        $sql = self::getSelect() . " WHERE id=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
            if ($retval instanceof MySqlLedgerEntry) {
                $retval->getValuesForForeignFields();
            }
        } catch (Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function getBalanceBeforeDate($date, $accountId = null): ?float
    {
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euroAmount),euroAmount,0),5)),2) AS balance
                FROM {$this->tableName()}
                WHERE entryDate<?" . (null !== $accountId ? " AND accountId=?" : "");
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
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
        } catch (Exception $ex) {
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
                throw new mysqli_sql_exception();
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $ex) {
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
        if (isset($this->currencyId)) {
            $this->currency = MySqlCurrency::getById($this->currencyId);
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
            (id, entryDate, categoryId, accountId, currencyId, direction, currencyAmount, euroAmount, remarks, username, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)
            ON DUPLICATE KEY UPDATE
                entryDate=VALUES(entryDate),
                categoryId=VALUES(categoryId),
                accountId=VALUES(accountId),
                currencyId=VALUES(currencyId),
                direction=VALUES(direction),
                currencyAmount=VALUES(currencyAmount),
                euroAmount=VALUES(euroAmount),
                remarks=VALUES(remarks),
                username=VALUES(username),
                updatedAt=NULL";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->bind_param(
                "isiisiddss",
                $this->id,
                $this->entryDate,
                $this->categoryId,
                $this->accountId,
                $this->currencyId,
                $this->direction,
                $this->currencyAmount,
                $this->euroAmount,
                $this->remarks,
                $this->username
            );
            $retval = $stmt->execute();
            $stmt->close();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        return false;
    }
}
