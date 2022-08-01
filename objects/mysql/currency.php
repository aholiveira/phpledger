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
    public string $description;
    public float $exchange_rate;
    protected static string $tableName = "moedas";

    public function __construct(mysqli $dblink)
    {
        parent::__construct($dblink);
    }
    public function getAll(array $field_filter = array()): array
    {
        $where = $this->getWhereFromArray($field_filter);
        $sql = "SELECT moeda_id as id, moeda_desc as `description`, taxa as exchange_rate FROM {$this->tableName()} {$where} ORDER BY moeda_desc";
        $retval = array();
        try {
            if (!is_object(static::$_dblink)) return $retval;
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }

    public function getById($id): currency
    {
        $sql = "SELECT moeda_id as id, moeda_desc as `description`, taxa as exchange_rate FROM {$this->tableName()} WHERE moeda_id=? ORDER BY moeda_desc";
        try {
            if (is_object(static::$_dblink)) {
                $stmt = @static::$_dblink->prepare($sql);
                if ($stmt == false) throw new mysqli_sql_exception();
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
                $stmt->close();
                if ($newobject instanceof currency) {
                    $this->copyfromObject($newobject);
                }
            }
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $this;
    }

    public function save(): bool
    {
        $retval = false;
        $sql = "SELECT moeda_id FROM {$this->tableName()} WHERE moeda_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("s", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id === $this->id) {
                $sql = "UPDATE {$this->tableName()} SET moeda_desc=?, taxa=? WHERE moeda_id=?";
            } else {
                $sql = "INSERT INTO {$this->tableName()} (moeda_desc, taxa, moeda_id) VALUES (?, ?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            $stmt->bind_param(
                "sds",
                $this->description,
                $this->exchange_rate,
                $this->id
            );
            $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
            $retval = true;
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getFreeId(string $field = "moeda_id"): int
    {
        return 0;
    }
}
