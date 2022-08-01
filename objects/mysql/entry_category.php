<?php

/**
 * entry_category object 
 * @property int id The ID of the object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class entry_category extends mysql_object implements iobject
{
    public ?string $description;
    public int $active;
    public ?int $parent_id;
    public ?string $parent_description;
    public array $children;
    protected static string $tableName = "tipo_mov";

    public function __construct(mysqli $dblink)
    {
        parent::__construct($dblink);
    }
    public function getAll(array $field_filter = array()): array
    {
        $where = $this->getWhereFromArray($field_filter);
        $sql = "SELECT tipo_id as id FROM {$this->tableName()} "
            . "{$where} "
            . "ORDER BY active desc, tipo_desc";
        $retval = array();
        try {
            if (!is_object(static::$_dblink)) return $retval;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->execute();
            $stmt->bind_result($id);
            while ($stmt->fetch()) {
                $newobject = new entry_category(static::$_dblink);
                $retval[$id] = $newobject;
            }
            $stmt->close();
            foreach ($retval as $id => $newobject) {
                $retval[$id] = $newobject->getById($id);
            }
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getBalance(): float
    {
        $balance = 0;
        $sql = "SELECT ABS(ROUND(SUM(ROUND(valor_euro,5)),2)) as balance 
            FROM movimentos
            WHERE tipo_mov=?
            GROUP BY tipo_mov";
        try {
            if (!is_object(static::$_dblink)) return $balance;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($balance);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $balance;
    }
    public function getById(int $id): entry_category
    {
        $sql = "SELECT tipo_id AS id, parent_id, tipo_desc AS `description`, active 
            FROM {$this->tableName()} 
            WHERE tipo_id=? ";
        if (!is_object(static::$_dblink)) return $this;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
            if ($newobject instanceof entry_category) {
                $this->copyfromObject($newobject);
                $this->getParentDescription();
                $this->children = $this->getChildren();
            }
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $this;
    }
    public function getParentDescription(): string
    {
        $sql = "SELECT tipo_desc AS `description`
            FROM {$this->tableName()} 
            WHERE tipo_id=?";
        if (!is_object(static::$_dblink) || !isset($this->parent_id)) return "";
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param("i", $this->parent_id);
            $stmt->execute();
            $stmt->bind_result($this->parent_description);
            $stmt->fetch();
            $stmt->close();
            return $this->parent_description;
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
            return "";
        }
    }
    public function getChildren(): array
    {
        $children = array();
        $sql = "SELECT tipo_id AS id 
            FROM {$this->tableName()} 
            WHERE parent_id=? 
            ORDER BY active desc, tipo_desc ";
        if (!is_object(static::$_dblink)) return $this;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($child_id);
            while ($stmt->fetch()) {
                $children[$child_id] = new entry_category(static::$_dblink);
            }
            $stmt->close();
            foreach ($children as $child_id => $newobject) {
                if ($newobject instanceof entry_category) {
                    $children[$child_id] = $newobject->getById($child_id);
                }
            }
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $children;
    }
    public function validate(): bool
    {
        $retval = true;
        $retval = ($this->id != $this->parent_id) && ($this->validation_message = "Categoria nao pode ser igual a si mesma") && $retval;
        $retval = is_int($this->id) && ($this->id > 0) && $retval;
        return $retval;
    }
    public function save(): bool
    {
        $retval = false;
        if (!$this->validate()) return $retval;
        $sql = "SELECT tipo_id FROM {$this->tableName()} WHERE tipo_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET parent_id=?, tipo_desc=?, active=? WHERE tipo_id=?";
            } else {
                $sql = "INSERT INTO {$this->tableName()} (parent_id, tipo_desc, active, tipo_id) VALUES (?, ?, ?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param(
                "ssss",
                $this->parent_id,
                $this->description,
                $this->active,
                $this->id
            );
            $retval = $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        $retval = false;
        $sql = "SELECT tipo_id FROM {$this->tableName()} WHERE tipo_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id === $this->id) {
                $sql = "DELETE FROM {$this->tableName()} WHERE tipo_id=?";
            } else {
                $sql = "";
            }
            $stmt->close();
            if (strlen($sql) == 0) return $retval;
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
            $retval = true;
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getFreeId(string $field = "tipo_id"): int
    {
        return parent::getFreeId($field);
    }
}
