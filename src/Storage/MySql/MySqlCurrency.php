<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\Currency;
use PHPLedger\Storage\MySql\MySqlObject;
use PHPLedger\Storage\MySql\Traits\MySqlSelectTrait;

class MySqlCurrency extends Currency
{
    use MySqlSelectTrait;
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
    }

    protected static string $tableName = "moedas";

    public function __construct()
    {
        $this->traitConstruct();
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'moeda_id' => 'code',
            'moeda_desc' => 'description',
            'taxa' => 'exchangeRate',
            'exchange_rate' => 'exchangeRate',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt'
        ];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL DEFAULT 0",
            "code" => "char(3) NOT NULL DEFAULT ''",
            "description" => "char(30) DEFAULT NULL",
            "exchangeRate" => "float(8,6) DEFAULT NULL",
            "username" => "char(255) DEFAULT ''",
            "createdAt" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()",
            "updatedAt" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()"
        ];
        $retval['primary_key'] = "moeda_id";
        return $retval;
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where} ORDER BY description";
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__)) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    private static function getByField($field, $value): ?Currency
    {
        $sql = self::getSelect() . " WHERE $field=? ORDER BY `description`";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getById($id): ?currency
    {
        return self::getByField("id", $id);
    }

    public static function getByCode($code): ?currency
    {
        return self::getByField("code", $code);
    }

    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                    (`description`, `exchangeRate`, `code`, `username`, `createdAt`, `updatedAt`, `id`)
                VALUES (?, ?, ?, ?, NULL, NULL, ?)
                ON DUPLICATE KEY UPDATE
                    `description`=VALUES(`description`),
                    `exchangeRate`=VALUES(`exchangeRate`),
                    `code`=VALUES(`code`),
                    `username`=VALUES(`username`),
                    `createdAt`=NULL,
                    `updatedAt`=NULL";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "sdssi",
                $this->description,
                $this->exchangeRate,
                $this->code,
                $this->username,
                $this->id
            );
            $retval = $stmt->execute();
            $stmt->close();
            if (!$retval) {
                throw new \mysqli_sql_exception();
            }
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
