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
    public ?string $description = null;
    public int $savings = 0;
    protected static string $tableName = "tipo_contas";
    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        static::getNextId();
    }

    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [];
        $retval['columns'] = [
            "tipo_id" => "int(2) NOT NULL DEFAULT 0",
            "tipo_desc" => "char(30) DEFAULT NULL",
            "savings" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "tipo_id";
        return $retval;
    }
    public static function getList(array $field_filter = []): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "SELECT tipo_id as id, tipo_desc as description, savings FROM " . static::tableName() . " {$where}";
        $retval = [];
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, [static::$_dblink])) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById(int $id): ?accounttype
    {
        $sql = "SELECT tipo_id as id, tipo_desc as description, savings FROM " . static::tableName() . " WHERE tipo_id=?";
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, [static::$_dblink]);
            $stmt->close();
            if (null === $retval) {
                $retval = new accounttype(static::$_dblink);
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }


    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                (`tipo_desc`, `savings`, `tipo_id`)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `tipo_desc` = VALUES(`tipo_desc`),
                    `savings` = VALUES(`savings`)";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "sii",
                $this->description,
                $this->savings,
                $this->id
            );
            $retval = $stmt->execute();
            $stmt->close();
            if (!$retval) {
                throw new \mysqli_sql_exception();
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        global $logger;
        $retval = false;
        try {
            $sql = "DELETE FROM {$this->tableName()} WHERE tipo_id=?";
            $stmt = static::$_dblink->prepare($sql);
            $stmt->bind_param("i", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getNextId(string $field = "tipo_id"): int
    {
        return parent::getNextId($field);
    }
}
