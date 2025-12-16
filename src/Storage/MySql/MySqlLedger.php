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
use PHPLedger\Domain\Ledger;
use PHPLedger\Services\Logger;
use PHPLedger\Storage\MySql\Traits\MySqlFetchAllTrait;
use PHPLedger\Storage\MySql\Traits\MySqlSelectTrait;

class MySqlLedger extends Ledger
{
    use MySqlSelectTrait;
    use MySqlFetchAllTrait;
    use MySqlObject {
        MySqlObject::getNextId as private traitGetNextId;
    }
    public string $name;
    protected static string $tableName = "`grupo_contas`";
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL DEFAULT 0",
            "nome" => "char(30) NOT NULL DEFAULT ''"
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    private static function getSelect(): string
    {
        return "SELECT id, nome as `name` FROM " . static::tableName();
    }
    private static function fetchOne(string $sql, array $params = []): ?self
    {
        $all = static::fetchAll($sql, $params);
        return empty($all) ? null : array_shift($all);
    }

    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where} ORDER BY id";
        Logger::instance()->dump($sql, __FUNCTION__ . " " . __CLASS__);
        return static::fetchAll($sql);
    }

    public static function getById($id): ?self
    {
        $sql = self::getSelect() . " WHERE id=?";
        Logger::instance()->dump($sql, __FUNCTION__ . " " . __CLASS__);
        return static::fetchOne($sql, [$id]);
    }

    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()} (nome, id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    nome=VALUES(nome),
                    id=VALUES(id)";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            if (strlen($this->name) > 30) {
                $this->name = substr($this->name, 0, 30);
            }
            $stmt->bind_param(
                "si",
                $this->name,
                $this->id
            );
            $retval = $stmt->execute();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        } finally {
            if (isset($stmt) && $stmt instanceof \mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    public function delete(): bool
    {
        return false;
    }
}
