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
    public static function getList(array $field_filter = array()): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "SELECT id FROM " . static::tableName() . " {$where} ORDER BY id";
        $retval = array();
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->execute();
            $stmt->bind_result($id);
            while ($stmt->fetch()) {
                $retval[$id] = null;
            }
            $stmt->close();
            foreach (array_keys($retval) as $id) {
                $retval[$id] = static::getById($id);
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById($id): ?ledger
    {
        $sql = "SELECT id, nome as `name` FROM " . static::tableName() . " WHERE id=?";
        try {
            if (!(static::$_dblink->ping())) {
                return null;
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $newobject;
    }

    public function update(): bool
    {
        $retval = false;
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
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
    public static function getNextId(string $field = "id"): int
    {
        return parent::getNextId($field);
    }
    public function delete(): bool
    {
        return false;
    }
}
