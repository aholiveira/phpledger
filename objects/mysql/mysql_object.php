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
    protected string $_errormessage;
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
    public function getNextId(string $field = "id"): int
    {
        $db = static::$_dblink;
        if (!is_object($db) || is_null(static::$tableName)) {
            return -1;
        }
        try {
            $sql = "SELECT `{$field}` FROM " . static::$tableName . " ORDER BY `{$field}`";
            $result = @$db->query($sql);
            if ($result == FALSE) $retval = -1;
            if ($result instanceof \mysqli_result) {
                if ($result->num_rows == 0) {
                    $retval = 1;
                }
                $row = $result->fetch_assoc();
                if ($result->num_rows == 1) {
                    $retval = ($row[$field] == 1 ? 2 : 1);
                }
                if ($result->num_rows > 1) {
                    $last = $row[$field];
                    $prev = 0;
                    while ($row && ((int)$last - (int)$prev) <= 1) {
                        $prev = $last;
                        $last = $row[$field];
                        $row = $result->fetch_assoc();
                    }
                    if ($last - $prev <= 1) {
                        $retval = $last + 1;
                    } else {
                        $retval = $prev + 1;
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        } finally {
            if (isset($result) && ($result instanceof \mysqli_result)) {
                $result->close();
            }
        }
        return $retval;
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
     * @param ?string $table_name table name to be used. if supplied where expression is built using "table_name.field_name" syntax
     * @return string SQL "WHERE" condition string built from the supplied values or an empty string
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
    abstract function getList(array $field_filter = array()): array;
    abstract function update(): bool;
    abstract function delete(): bool;
    /**
     * Validates object data.
     * Descendant classes should implement their own code.
     * 
     * @return bool TRUE if object is valid. FALSE otherwise
     */
    public function validate(): bool
    {
        return TRUE;
    }
    public function error_message(): string
    {
        return isset($this->_errormessage) ? $this->_errormessage : "";
    }
    protected function setErrorMessage(string $message)
    {
        $this->_errormessage = $message;
    }
    protected function tableName(): string
    {
        return static::$tableName;
    }
    protected function handleException(\Exception $ex, $sql = "")
    {
        print_var($this, "THIS", true);
        print_var(static::$_dblink, "DBLINK", true);
        print_var($sql, "SQL", true);
        print_var($ex, "EXCEPTION", true);
        debug_print($ex->getMessage());
        debug_print($ex->getTraceAsString());
        $this->setErrorMessage($ex->getTraceAsString());
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
