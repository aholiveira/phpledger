<?php

/**
 * entry_category object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class entry_category extends mysql_object implements iObject
{
    public ?string $description;
    public int $active;
    public ?int $parent_id;
    public ?string $parent_description = null;
    public array $children;
    protected static string $tableName = "tipo_mov";
    public string $validation_message;

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        $this->children = [];
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['columns'] = [
            "tipo_id" => "int(3) NOT NULL DEFAULT 0",
            "parent_id" => "int(3) DEFAULT NULL",
            "tipo_desc" => "char(50) DEFAULT NULL",
            "active" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "tipo_id";
        $retval['keys'] = ["parent_id" => "parent_id"];
        $retval['constraints'] = ["parent_id" => "`tipo_mov` (`tipo_id`) ON DELETE CASCADE ON UPDATE CASCADE"];
        return $retval;
    }
    public static function getList(array $field_filter = []): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "WITH RECURSIVE category_tree AS (
            SELECT tipo_id AS id, parent_id, tipo_desc AS description, active
            FROM " . static::$tableName . "
            {$where}
            UNION ALL
            SELECT c.tipo_id, c.parent_id, c.tipo_desc, c.active
            FROM " . static::$tableName . " c
            INNER JOIN category_tree ct ON c.parent_id = ct.id
        )
        SELECT * FROM category_tree ORDER BY active DESC, description";
        $retval = [];
        $children_map = [];

        try {
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_object(__CLASS__, [static::$_dblink])) {
                if ($row->parent_id === 0 || $row->parent_id === null) {
                    $retval[$row->id] = $row;
                } else {
                    $children_map[$row->parent_id][$row->id] = $row;
                }
            }
            $stmt->close();
            foreach ($children_map as $parent_id => $child_objects) {
                if (isset($retval[$parent_id])) {
                    $retval[$parent_id]->children = $child_objects;
                    $retval[$parent_id]->setChildDescriptions();
                }
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    private function setChildDescriptions(): void
    {
        foreach ($this->children as $key => $child) {
            $this->children[$key]->parent_description = $this->description;
        }
    }
    public function getBalance(): float
    {
        $retval = 0;
        $sql = "SELECT ABS(ROUND(SUM(ROUND(euro_amount,5)),2)) as balance
            FROM movimentos
            WHERE category_id=?
            GROUP BY category_id";
        try {
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getById(int $id): self
    {
        $sql = "SELECT c.tipo_id AS id, c.parent_id, c.tipo_desc AS `description`, c.active, p.tipo_desc AS parent_description
            FROM " . static::tableName() . " c
            LEFT JOIN " . static::tableName() . " p ON c.parent_id = p.tipo_id
            WHERE c.tipo_id=? OR c.parent_id=?";
        $children = [];
        $retval = new self(static::$_dblink);
        try {
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("ii", $id, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_object(__CLASS__, [static::$_dblink])) {
                if ($row->id === $id) {
                    $retval = $row;
                } else {
                    $children[$row->id] = $row;
                }
            }
            $stmt->close();
            $retval->children = $children;
            $retval->setChildDescriptions();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function validate(): bool
    {
        if ($this->id === null || $this->id < 0) {
            return false;
        }
        if ($this->id === $this->parent_id) {
            $this->validation_message = "Categoria nao pode ser igual a si mesma";
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
            $sql = "INSERT INTO {$this->tableName()} (parent_id, tipo_desc, active, tipo_id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    parent_id=VALUES(parent_id),
                    tipo_desc=VALUES(tipo_desc),
                    active=VALUES(active)";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("isii", $this->parent_id, $this->description, $this->active, $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        $retval = false;
        try {
            $sql = "DELETE FROM {$this->tableName()} WHERE tipo_id=?";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getNextId(string $field = "tipo_id"): int
    {
        return parent::getNextId($field);
    }
}
