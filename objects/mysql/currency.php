<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class currency extends mysql_object implements iobject
{
    public string $code;
    public string $description;
    public float $exchange_rate;
    public string $username = "";
    public string $created_at;
    public string $updated_at;

    protected static string $tableName = "moedas";

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        $this->code = "";
    }
    public static function getList(array $field_filter = array()): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "SELECT id, `code`, `description`, exchange_rate, username, created_at, updated_at FROM " . static::tableName() . " {$where} ORDER BY description";
        $retval = [];
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById($id): ?currency
    {
        $sql = "SELECT id, `code`, `description`, exchange_rate, username, created_at, updated_at FROM " . static::tableName() . " WHERE id=? ORDER BY `description`";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getByCode($code): ?currency
    {
        $sql = "SELECT id, `code`, `description`, exchange_rate, username, created_at, updated_at FROM " . static::tableName() . " WHERE code=? ORDER BY `description`";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public function update(): bool
    {
        $retval = false;
        $sql = "SELECT `id` FROM {$this->tableName()} WHERE id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                return $retval;
            if (!isset($this->id))
                return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            null !== $stmt->fetch() && $return_id === $this->id ?
                $sql = "UPDATE {$this->tableName()} SET `description`=?, exchange_rate=?, code=?, username=?, updated_at=NULL WHERE id=?"
                :
                $sql = "INSERT INTO {$this->tableName()} (`description`, exchange_rate, code, username, created_at, updated_at, id) VALUES (?, ?, ?, ?, NULL, NULL, ?)"
            ;
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            $stmt->bind_param(
                "sdsss",
                $this->description,
                $this->exchange_rate,
                $this->code,
                $this->username,
                $this->id
            );
            $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
            $retval = true;
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
