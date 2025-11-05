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
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            'moeda_id' => 'code',
            'moeda_desc' => 'description',
            'taxa' => 'exchange_rate'
        ];
        $retval['columns'] = [
            "id" => "int(4) NOT NULL DEFAULT 0",
            "code" => "char(3) NOT NULL DEFAULT ''",
            "description" => "char(30) DEFAULT NULL",
            "exchange_rate" => "float(8,6) DEFAULT NULL",
            "username" => "char(255) DEFAULT ''",
            "created_at" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()",
            "updated_at" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()"
        ];
        $retval['primary_key'] = "moeda_id";
        return $retval;
    }
    public static function getList(array $field_filter = []): array
    {
        $where = static::getWhereFromArray($field_filter);
        $sql = "SELECT id, `code`, `description`, exchange_rate, username, created_at, updated_at FROM " . static::tableName() . " {$where} ORDER BY description";
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

    public static function getById($id): ?currency
    {
        $sql = "SELECT id, `code`, `description`, exchange_rate, username, created_at, updated_at FROM " . static::tableName() . " WHERE id=? ORDER BY `description`";
        $retval = null;
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
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, [static::$_dblink]);
            $stmt->close();
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
                    (`description`, `exchange_rate`, `code`, `username`, `created_at`, `updated_at`, `id`)
                VALUES (?, ?, ?, ?, NULL, NULL, ?)
                ON DUPLICATE KEY UPDATE
                    `description`=VALUES(`description`),
                    `exchange_rate`=VALUES(`exchange_rate`),
                    `code`=VALUES(`code`),
                    `username`=VALUES(`username`),
                    `created_at`=NULL,
                    `updated_at`=NULL";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "sdssi",
                $this->description,
                $this->exchange_rate,
                $this->code,
                $this->username,
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
        return false;
    }
}
