<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use mysqli_result;
use mysqli_stmt;
use PHPLedger\Domain\EntryCategory;

class MySqlEntryCategory extends EntryCategory
{
    protected static string $tableName = "tipo_mov";
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
        MySqlObject::getNextId as private traitGetNextId;
    }
    public function __construct()
    {
        $this->traitConstruct();
        $this->children = [];
        if (!isset($this->active)) {
            $this->active = 0;
        }
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'tipo_id' => 'id',
            'parent_id' => 'parentId',
            'tipo_desc' => 'description'
        ];
        $retval['columns'] = [
            "id" => "int(3) NOT NULL DEFAULT 0",
            "parentId" => "int(3) DEFAULT NULL",
            "description" => "char(50) DEFAULT NULL",
            "active" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "id";
        $retval['keys'] = ["parentId" => "parentId"];
        $retval['constraints'] = ["parentId" => "`tipo_mov` (`id`) ON DELETE CASCADE ON UPDATE CASCADE"];
        return $retval;
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = "WITH RECURSIVE category_tree AS (
            SELECT id, parentId, description, active
            FROM " . static::$tableName . "
            {$where}
            UNION ALL
            SELECT c.id, c.parentId, c.description, c.active
            FROM " . static::$tableName . " c
            INNER JOIN category_tree ct ON c.parentId = ct.id
        )
        SELECT * FROM category_tree ORDER BY active DESC, description";
        $retval = [];
        $children_map = [];

        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_object(__CLASS__)) {
            if ($row->parentId === 0 || $row->parentId === null) {
                $retval[$row->id] = $row;
            } else {
                $children_map[$row->parentId][$row->id] = $row;
            }
        }
        $stmt->close();
        foreach ($children_map as $parentId => $child_objects) {
            if (isset($retval[$parentId])) {
                $retval[$parentId]->children = $child_objects;
                $retval[$parentId]->setChildDescriptions();
            }
        }
        return $retval;
    }
    private function setChildDescriptions(): void
    {
        foreach ($this->children as $key => $child) {
            $this->children[$key]->parentDescription = $this->description;
        }
    }
    public function getBalance(): float
    {
        $retval = 0;
        $sql = "SELECT ABS(ROUND(SUM(ROUND(euroAmount,5)),2)) as balance
            FROM movimentos
            WHERE categoryId=?
            GROUP BY categoryId";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $stmt->bind_result($retval);
        $stmt->fetch();
        $stmt->close();
        return $retval;
    }
    public static function getById(int $id): self
    {
        $sql = "SELECT c.id, c.parentId, c.description AS `description`, c.active, p.description AS parentDescription
            FROM " . static::tableName() . " c
            LEFT JOIN " . static::tableName() . " p ON c.parentId = p.id
            WHERE c.id=? OR c.parentId=?";
        $children = [];
        $retval = new self();
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_object(__CLASS__)) {
            if ($row->id === $id) {
                $retval = $row;
            } else {
                $children[$row->id] = $row;
            }
        }
        $stmt->close();
        $retval->children = $children;
        $retval->setChildDescriptions();
        return $retval;
    }
    public function validate(): bool
    {
        if ($this->id === null || $this->id < 0) {
            return false;
        }
        if ($this->id === $this->parentId) {
            $this->validationMessage = "Categoria nao pode ser igual a si mesma";
            return false;
        }
        return true;
    }
    public function update(): bool
    {
        $retval = false;
        if (!$this->validate()) {
            return $retval;
        }
        try {
            $sql = "INSERT INTO {$this->tableName()} (parentId, `description`, active, id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    parentId=VALUES(parentId),
                    `description`=VALUES(`description`),
                    active=VALUES(active)";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($this->id === null) {
                $this->id = $this->getNextId();
            }
            $stmt->bind_param("ssss", $this->parentId, $this->description, $this->active, $this->id);
            $retval = $stmt->execute();
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    public function delete(): bool
    {
        $retval = false;
        $sql = "DELETE FROM {$this->tableName()} WHERE id=?";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("i", $this->id);
        $retval = $stmt->execute();
        $stmt->close();
        return $retval;
    }
}
