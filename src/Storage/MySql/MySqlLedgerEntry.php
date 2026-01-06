<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\LedgerEntry;

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
        return [
            "id",
            "entryDate",
            "categoryId",
            "accountId",
            "currencyId",
            "direction",
            "currencyAmount",
            "euroAmount",
            "exchangeRate",
            "a_pagar",
            "com_talao",
            "remarks",
            "username",
            "createdAt",
            "updatedAt"
        ];
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where} ORDER BY entryDate, id";
        $retval = [];
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($newobject = $result->fetch_object(__CLASS__)) {
            $newobject->getValuesForForeignFields();
            $retval[$newobject->id] = $newobject;
        }
        $stmt->close();
        return $retval;
    }

    public static function getById($id): ?self
    {
        $sql = self::getSelect() . " WHERE id=?";
        $retval = null;
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        if ($retval instanceof MySqlLedgerEntry) {
            $retval->getValuesForForeignFields();
        }
        return $retval;
    }
    public function getBalanceBeforeDate($date, $accountId = null): ?float
    {
        $retval = null;
        $sql = "SELECT ROUND(SUM(ROUND(IF(NOT ISNULL(euroAmount),euroAmount,0),5)),2) AS balance
                FROM {$this->tableName()}
                WHERE entryDate<?" . (null !== $accountId ? " AND accountId=?" : "");
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        if (null === $accountId) {
            $stmt->bind_param("s", $date);
        } else {
            $stmt->bind_param("si", $date, $accountId);
        }
        $stmt->execute();
        $stmt->bind_result($retval);
        $stmt->fetch();
        $stmt->close();
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
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($retval);
        $stmt->fetch();
        $stmt->close();
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
        if (!$this->validate()) {
            return false;
        }
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
        return $this->saveWithTransaction(
            $sql,
            "siisiddss",
            [
                $this->entryDate,
                $this->categoryId,
                $this->accountId,
                $this->currencyId,
                $this->direction,
                $this->currencyAmount,
                $this->euroAmount,
                $this->remarks,
                $this->username,
            ]
        );
    }
    public function delete(): bool
    {
        return false;
    }
}
