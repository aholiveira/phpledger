<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class ledger extends mysql_object implements iobject
{
    public string $name;
    protected static string $tableName = "`grupo_contas`";
    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
    }
    public function getAll(array $field_filter = array()): array
    {
        $where = $this->getWhereFromArray($field_filter);
        $sql = "SELECT id FROM {$this->tableName()} {$where} ORDER BY id";
        $retval = array();
        try {
            if (!is_object(static::$_dblink)) return $retval;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->execute();
            $stmt->bind_result($id);
            while ($stmt->fetch()) {
                $newobject = new ledger(static::$_dblink);
                $retval[$id] = $newobject;
            }
            $stmt->close();
            foreach ($retval as $id => $newobject) {
                $retval[$id] = $newobject->getById($id);
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }

    public function getById($id): ledger
    {
        $sql = "SELECT id, nome as `name` FROM {$this->tableName()} WHERE id=?";
        if (!is_object(static::$_dblink)) return $this;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
            if ($newobject instanceof ledger) {
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
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET nome=? WHERE id=?";
            } else {
                $sql = "INSERT INTO {$this->tableName()} (nome, id) VALUES (?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param(
                "ss",
                $this->name,
                $this->id
            );
            $retval = $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            print "ERROR";
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getFreeId(string $field = "id"): int
    {
        return parent::getFreeId($field);
    }
}
