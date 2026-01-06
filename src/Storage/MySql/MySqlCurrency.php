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
        return [
            "id",
            "code",
            "description",
            "exchangeRate",
            "username",
            "createdAt",
            "updatedAt"
        ];
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where} ORDER BY description";
        $retval = [];
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($newobject = $result->fetch_object(__CLASS__)) {
            $retval[$newobject->id] = $newobject;
        }
        $stmt->close();
        return $retval;
    }

    private static function getByField($field, $value): ?self
    {
        $sql = self::getSelect() . " WHERE $field=? ORDER BY `description`";
        $retval = null;
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        return $retval;
    }
    public static function getById($id): ?self
    {
        return self::getByField("id", $id);
    }

    public static function getByCode($code): ?self
    {
        return self::getByField("code", $code);
    }

    public function update(): bool
    {
        $sql = "INSERT INTO {$this->tableName()}
                    (`id`, `description`, `exchangeRate`, `code`, `username`, `createdAt`, `updatedAt`)
                VALUES (?, ?, ?, ?, ?, NULL, NULL)
                ON DUPLICATE KEY UPDATE
                    `description`=VALUES(`description`),
                    `exchangeRate`=VALUES(`exchangeRate`),
                    `code`=VALUES(`code`),
                    `username`=VALUES(`username`),
                    `createdAt`=NULL,
                    `updatedAt`=NULL";
        return $this->saveWithTransaction(
            $sql,
            "sdss",
            [
                $this->description,
                $this->exchangeRate,
                $this->code,
                $this->username,
            ]
        );
    }
    public function delete(): bool
    {
        return false;
    }
}
