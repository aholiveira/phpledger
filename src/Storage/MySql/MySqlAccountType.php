<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use \PHPLedger\Domain\AccountType;
class MysqlAccountType extends AccountType
{
    protected static string $tableName = "tipo_contas";
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
        MySqlObject::getNextId as private traitGetNextId;
    }
    public function __construct()
    {
        $this->traitConstruct();
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
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__)) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }

    public static function getById(int $id): ?AccountType
    {
        $sql = "SELECT tipo_id as id, tipo_desc as description, savings FROM " . static::tableName() . " WHERE tipo_id=?";
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
            if (null === $retval) {
                $retval = new self();
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
            $stmt = MySqlStorage::getConnection()->prepare($sql);
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
        $retval = false;
        try {
            $sql = "DELETE FROM {$this->tableName()} WHERE tipo_id=?";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
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
        return self::traitGetNextId($field);
    }
}
