<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\Ledger;
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
        return [
            "id",
            "nome"
        ];
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
        return static::fetchAll($sql);
    }

    public static function getById($id): ?self
    {
        $sql = self::getSelect() . " WHERE id=?";
        return static::fetchOne($sql, [$id]);
    }

    public function update(): bool
    {
        $sql = "INSERT INTO {$this->tableName()} (id, nome)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    id=VALUES(id),
                    nome=VALUES(nome)";
        if (strlen($this->name) > 30) {
            $this->name = substr($this->name, 0, 30);
        }
        return $this->saveWithTransaction($sql, "s", [$this->name]);
    }
    public function delete(): bool
    {
        return false;
    }
}
