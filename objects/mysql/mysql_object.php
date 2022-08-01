<?php

/**
 * Implements basic functionally and holds common code for mysql-backed data objects 
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 * 
 */

abstract class mysql_object implements iobject
{
    public $id;
    protected static \mysqli $_dblink;
    protected static string $tableName;
    public function __construct(\mysqli $dblink)
    {
        static::$_dblink = $dblink;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return isset($this->id) ? $this->id : null;
    }
    /**
     * Copies the object vars from $object into $this
     */
    protected function copyfromObject(mysql_object $object): void
    {
        $vars = is_object($object) ? get_object_vars($object) : $object;
        if (!is_array($vars)) throw new \Exception('no props to import into the object!');
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }
    /**
     * @return int The next free number on $field. It fills gaps if there are any.
     */
    public function getFreeId(string $field = "id"): int
    {
        try {
            $db = static::$_dblink;
            $fld = $field;
            if (!is_object($db) || is_null(static::$tableName)) {
                return -1;
            }
            $sql = "SELECT {$field} FROM " . static::$tableName . " ORDER BY {$field}";
            $result = @$db->query($sql);
            if (!$result) return -1;
            $row = $result->fetch_assoc();
            if ($result->num_rows == 0) {
                $result->close();
                return 1;
            }
            if ($result->num_rows == 1) {
                $result->close();
                return ($row[$fld] == 1 ? 2 : 1);
            }
            $last = $row[$fld];
            $prev = 0;
            while ($row = @$result->fetch_assoc() and ((int)$last - (int)$prev) <= 1) {
                $prev = $last;
                $last = $row[$fld];
            }
            $result->close();
            if ($last - $prev <= 1)
                return $last + 1;
            else
                return $prev + 1;
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
    }
    public function __toString()
    {
        return get_called_class();
    }
    /**
     * 
     * @param array $field_filter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where 
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     * 
     * @return string where condition string built from the supplied values or an empty string
     */
    protected static function getWhereFromArray(array $field_filter, ?string $table_name = null): string
    {
        $where = "";
        foreach ($field_filter as $field => $filter) {
            if (strlen($where) > 0) $where .= " AND ";
            $field_name = is_null($table_name) ? "`{$field}`" : "`{$table_name}`.`{$field}`";
            $where .= "{$field_name} {$filter['operator']} {$filter['value']}";
        }
        if (strlen($where) > 0) $where = "WHERE {$where}";
        return $where;
    }
    abstract function getById(int $id): mysql_object;
    /**
     * @param array $field_filter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where 
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     */
    abstract function getAll(array $field_filter = array()): array;
    abstract function save(): bool;
    protected function tableName(): string
    {
        return static::$tableName;
    }
    protected function handleException($ex, $sql = "")
    {
        print_var($this, "THIS", true);
        print_var(static::$_dblink, "DBLINK", true);
        print_var($sql, "SQL", true);
        print_var($ex, "EXCEPTION", true);
        debug_print($ex->getMessage());
        debug_print($ex->getTraceAsString());
    }
    public function clear(): void
    {
        $vars = get_object_vars($this);
        if (!is_array($vars)) throw new \Exception('no props to import into the object!');
        foreach (array_keys($vars) as $key) {
            unset($this->$key);
        }
    }
}
