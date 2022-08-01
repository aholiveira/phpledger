<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class accounttype extends mysql_object implements iobject
{
    public string $description;
    public int $savings;
    protected static string $tableName = "tipo_contas";
    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
    }

    public function getAll(array $field_filter = array()): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "SELECT tipo_id as id, tipo_desc as description, savings FROM {$this->tableName()} {$where}";
        $retval = array();
        try {
            if (!is_object(static::$_dblink)) return $retval;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception();
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }

    public function getById(int $id): accounttype
    {
        $sql = "SELECT tipo_id as id, tipo_desc as description, savings FROM {$this->tableName()} WHERE tipo_id=?";
        try {
            if (!is_object(static::$_dblink)) return $this;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception();
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
            if ($newobject instanceof accounttype) {
                $this->copyfromObject($newobject);
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $this;
    }


    public function save(): bool
    {
        $retval = false;
        $sql = "SELECT tipo_id FROM {$this->tableName()} WHERE tipo_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET tipo_desc=?, savings=? WHERE tipo_id=?";
            } else {
                $sql = "INSERT INTO {$this->tableName()} (tipo_desc, savings, tipo_id) VALUES (?, ?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception();
            $stmt->bind_param(
                "sii",
                $this->description,
                $this->savings,
                $this->id
            );
            $retval = $stmt->execute();
            if ($stmt == false) throw new \mysqli_sql_exception();
            $stmt->close();
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            static::$_dblink->rollback();
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
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "DELETE FROM {$this->tableName()} WHERE tipo_id=?";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) {
                print_var(static::$_dblink);
                debug_print(static::$_dblink->errno);
            }
            $stmt->bind_param("i", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
            $this->clear();
        } catch (\Exception $ex) {
            static::$_dblink->rollback();
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getFreeId(string $field = "tipo_id"): int
    {
        $retval = parent::getFreeId($field);
        return $retval;
    }
}
