<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\MySql;

use DomainException;
use PHPLedger\Contracts\DataObjectInterface;
use Throwable;

trait MySqlObject
{
    protected static string $errorMessage;
    public function __construct()
    {
        if (!isset($this->id)) {
            $this->id = null;
        }
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function create(): DataObjectInterface
    {
        $this->update();
        return $this;
    }
    public function read(int $id): ?DataObjectInterface
    {
        return $this->getById($id);
    }

    /**
     * Copies the object vars from $object into $this
     */
    protected static function copyfromObject(MySqlObject $source, MySqlObject $destination): void
    {
        $vars = \is_object($source) ? get_object_vars($source) : $source;
        if (!\is_array($vars)) {
            throw new DomainException('no props to import into the object!');
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
        $table = static::$tableName;
        $sql = "SELECT MIN(t1.$field + 1) AS next_id
            FROM $table t1
            LEFT JOIN $table t2 ON t2.$field = t1.$field + 1
            WHERE t2.$field IS NULL";
        $res = $db->query($sql);
        if (!$res) {
            return 1;
        }
        $row = $res->fetch_assoc();
        return $row['next_id'] ?? 1;
    }
    public function __toString()
    {
        return get_called_class();
    }
    /**
     *
     * @param array $fieldFilter an array of the form ('field_name' => array('operator' => SQL operator, 'value' => value to filter by))
     * - where
     * - - field_name is a field which you want to filter by
     * - - operator is any valid SQL operator (LIKE, BETWEEN, <, >, <=, =>)
     * - - value is the value to be filtered
     * @param ?string $table_name table name to be used. if supplied where expression is built using "table_name.field_name" syntax
     * @return string SQL "WHERE" condition string built from the supplied values or an empty string
     */
    protected static function getWhereFromArray(array $fieldFilter, ?string $table = null): string
    {
        if (!$fieldFilter) {
            return "";
        }

        $db = MySqlStorage::getConnection();
        $allowed = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'BETWEEN', 'IS', 'IN'];

        $escape = fn($v) => "'" . $db->real_escape_string($v) . "'";
        $fieldName = fn($f) => $table ? "`$table`.`$f`" : "`$f`";

        $parts = [];

        foreach ($fieldFilter as $field => $filter) {
            $op = strtoupper($filter['operator']);
            if (!in_array($op, $allowed)) {
                continue;
            }

            $name = $fieldName($field);
            $val = $filter['value'];

            $sql = self::buildCondition($name, $op, $val, $escape);
            if ($sql) {
                $parts[] = $sql;
            }
        }

        return $parts ? "WHERE " . implode(" AND ", $parts) : "";
    }

    private static function buildCondition(string $name, string $op, mixed $val, callable $escape): ?string
    {
        return match ($op) {
            'BETWEEN' => self::handleBetween($name, $val, $escape),
            'IN'      => self::handleIn($name, $val, $escape),
            'IS', '=', '!=', '<', '>', '<=', '>=', 'LIKE' => self::handleDefault($name, $op, $val, $escape),
            default   => null,
        };
    }

    private static function handleBetween(string $name, mixed $val, callable $escape): ?string
    {
        if (!is_array($val) || count($val) < 2) {
            return null;
        }
        return "$name BETWEEN {$escape($val[0])} AND {$escape($val[1])}";
    }

    private static function handleIn(string $name, mixed $val, callable $escape): string
    {
        $vals = is_array($val) ? $val : [$val];
        $list = implode(',', array_map($escape, $vals));
        return "$name IN ($list)";
    }

    private static function handleDefault(string $name, string $op, mixed $val, callable $escape): string
    {
        $valueSql = ($val === null && $op === "IS") ? "NULL" : $escape($val);
        return "$name $op $valueSql";
    }
    abstract public static function getList(array $fieldFilter = []): array;
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
    public function clear(): void
    {
        $vars = get_object_vars($this);
        if (!is_array($vars)) {
            throw new DomainException('no props to import into the object!');
        }
        foreach (array_keys($vars) as $key) {
            unset($this->$key);
        }
    }
    protected function saveWithTransaction(string $sql, string $types, array $params): bool
    {
        $c = MySqlStorage::getConnection();
        try {
            $c->begin_transaction();
            if (!isset($this->id) || $this->id === 0) {
                $this->id = $this->getNextId();
            }
            $stmt = $c->prepare($sql);
            $stmt->bind_param("i" . $types, $this->id, ...$params);
            $ok = $stmt->execute();
            $stmt->close();
            $c->commit();
            return $ok;
        } catch (Throwable $e) {
            $c->rollback();
            static::setErrorMessage($e->getMessage() ?? '');
            throw $e;
        }
    }
}
