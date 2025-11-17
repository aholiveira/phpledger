<?php

/**
 * Implements basic functionally and holds common code for mysql-backed data objects
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use PHPLedger\Util\Logger;

trait MySqlObject
{
    protected static string $errorMessage;
    protected static \mysqli $dbConnection;
    public function __construct()
    {
        static::$dbConnection = MySqlStorage::getConnection();
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id ?? $this->id;
    }
    /**
     * Copies the object vars from $object into $this
     */
    protected static function copyfromObject(MySqlObject $source, MySqlObject $destination): void
    {
        $vars = is_object($source) ? get_object_vars($source) : $source;
        if (!is_array($vars)) {
            throw new \Exception('no props to import into the object!');
        }
        foreach ($vars as $key => $value) {
            $destination->$key = $value;
        }
    }
    /**
     * @return int The next free number on $field. It fills gaps if there are any.
     */
    public static function getNextId(string $field = "id"): int
    {
        $db = MySqlStorage::getConnection();
        $retval = -1;
        if (null === static::$tableName) {
            return $retval;
        }
        try {
            $sql = "SELECT `{$field}` FROM " . static::$tableName . " ORDER BY `{$field}`";
            $result = @$db->query($sql);
            if (!$result || !($result instanceof \mysqli_result)) {
                return $retval;
            }
            if ($result->num_rows === 0) {
                return 1;
            }
            $row = $result->fetch_assoc();
            if ($result->num_rows == 1) {
                return $row[$field] == 1 ? 2 : 1;
            }
            if ($result->num_rows > 1) {
                $last = $row[$field];
                $prev = 0;
                while ($row && ((int) $last - (int) $prev) <= 1) {
                    $prev = $last;
                    $last = $row[$field];
                    $row = $result->fetch_assoc();
                }
                $retval = (($last - $prev <= 1) ? $last : $prev) + 1;
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
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
            if (strlen($where) > 0) {
                $where .= " AND ";
            }
            $field_name = null === $table_name ? "`{$field}`" : "`{$table_name}`.`{$field}`";
            $where .= "{$field_name} {$filter['operator']} {$filter['value']}";
        }
        if (strlen($where) > 0) {
            $where = "WHERE {$where}";
        }
        return $where;
    }
    /**
     * @param array $field_filter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     */
    abstract public static function getList(array $field_filter = []): array;
    abstract public static function getDefinition(): array;
    abstract public function update(): bool;
    abstract public function delete(): bool;
    /**
     * Validates object data.
     * Descendant classes should implement their own code.
     *
     * @return bool true if object is valid. false otherwise
     */
    public function validate(): bool
    {
        return true;
    }
    public function errorMessage(): string
    {
        return isset(static::$errorMessage) ? static::$errorMessage : "";
    }
    protected static function setErrorMessage(string $message)
    {
        static::$errorMessage = $message;
    }
    protected static function tableName(): string
    {
        return static::$tableName;
    }
    protected static function handleException(\Exception $ex, $sql = "")
    {
        Logger::instance()->dump(static::$dbConnection, "DBLINK");
        Logger::instance()->dump($sql, "SQL");
        Logger::instance()->dump($ex, "EXCEPTION");
        Logger::instance()->dump($ex->getMessage(), "EXMSG");
        Logger::instance()->dump($ex->getTraceAsString(), "TRACE");
        static::setErrorMessage($ex->getTraceAsString());
    }
    public function clear(): void
    {
        $vars = get_object_vars($this);
        if (!is_array($vars)) {
            throw new \Exception('no props to import into the object!');
        }
        foreach (array_keys($vars) as $key) {
            unset($this->$key);
        }
    }
}
