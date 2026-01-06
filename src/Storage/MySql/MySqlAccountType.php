<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\AccountType;
use PHPLedger\Storage\MySql\Traits\MySqlDeleteTrait;
use PHPLedger\Storage\MySql\Traits\MySqlSelectTrait;

class MysqlAccountType extends AccountType
{
    protected static string $tableName = "tipo_contas";
    use MySqlSelectTrait;
    use MySqlDeleteTrait;
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
        MySqlObject::getNextId as private traitGetNextId;
    }
    public function __construct()
    {
        $this->traitConstruct();
    }

    public static function getDefinition(): array
    {
        return [
            "id",
            "description",
            "savings"
        ];
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where}";
        $retval = [];
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($newobject = $result->fetch_object(__CLASS__)) {
            $retval[$newobject->id] = $newobject;
        }
        $stmt->close();
        return $retval;
    }

    public static function getById(int $id): ?self
    {
        $sql = self::getSelect() . " WHERE id=?";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        if (null === $retval) {
            $retval = new self();
        }
        return $retval;
    }

    public function update(): bool
    {
        $sql = "INSERT INTO {$this->tableName()}
                (`id`, `description`, `savings`)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `description` = VALUES(`description`),
                    `savings` = VALUES(`savings`)";
        return $this->saveWithTransaction(
            $sql,
            "si",
            [
                $this->description,
                $this->savings,
            ]
        );
    }
}
