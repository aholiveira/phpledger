<?php

/**
 * Holds a mysql-backed `account` object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use DateTimeInterface;
use PHPLedger\Domain\Account;
use PHPLedger\Storage\MySql\Traits\MySqlDeleteTrait;
use PHPLedger\Storage\MySql\Traits\MySqlFetchAllTrait;
use PHPLedger\Storage\MySql\Traits\MySqlSelectTrait;

class MySqlAccount extends Account
{
    use MySqlSelectTrait;
    use MySqlFetchAllTrait;
    use MySqlDeleteTrait;
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
    }
    protected static string $tableName = "contas";

    private static function fetchOne(string $sql, array $params = []): self
    {
        $all = static::fetchAll($sql, $params);
        return array_shift($all) ?: new MySqlAccount();
    }

    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql =  self::getSelect() . " {$where} ORDER BY active DESC, name";
        return static::fetchAll($sql);
    }

    public static function getById($id): self
    {
        $sql = self::getSelect() . " WHERE id=?";
        return static::fetchOne($sql, [$id]);
    }

    public static function getDefinition(): array
    {
        $retval = [];
        $retval['columns'] = [
            "id" => "int(3) NOT NULL DEFAULT 0",
            "number" => "char(30) NOT NULL DEFAULT ''",
            "name" => "char(30) NOT NULL DEFAULT ''",
            "grupo" => "int(3) NOT NULL DEFAULT 0",
            "typeId" => "int(2) DEFAULT NULL",
            "iban" => "char(24) DEFAULT NULL",
            "swift" => "char(24) NOT NULL DEFAULT ''",
            "openDate" => "date DEFAULT NULL",
            "closeDate" => "date DEFAULT NULL",
            "active" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "id";
        $retval['new'] = [
            'group' => 'grupo',
            'type_id' => 'typeId',
            'open_date' => 'openDate',
            'close_date' => 'closeDate',
            'conta_abertura' => 'openDate',
            'conta_fecho' => 'closeDate',
            'conta_num' => 'number',
            'conta_nome' => 'name',
            'tipo_id' => 'typeId',
            'conta_nib' => 'iban',
            'conta_id' => 'id'
        ];
        return $retval;
    }
    /**
     * @return array array with keys 'income', 'expense' and 'balance'
     * representing the corresponding amounts (euro-based) for that account and
     * on or before the reference date
     */
    public function getBalanceOnDate(DateTimeInterface $date): array
    {
        return $this->getBalance(null, $date);
    }
    public function getBalance(?DateTimeInterface $startDate = null, ?DateTimeInterface $endDate = null): array
    {
        $where = "accountId=? ";
        $param_array = [$this->id];
        if (null !== $startDate) {
            $where .= " AND `entryDate`>=? ";
            $param_array[] = $startDate->format("Y-m-d");
        }
        if (null !== $endDate) {
            $where .= " AND entryDate<=? ";
            $param_array[] = $endDate->format("Y-m-d");
        }
        $sql = "SELECT
                SUM(ROUND(IF(direction='1',euroAmount,0),2)) AS income,
                SUM(ROUND(IF(direction='-1',-euroAmount,0),2)) AS expense,
                ROUND(SUM(ROUND(IF(NOT ISNULL(euroAmount),euroAmount,0),5)),2) AS balance
                FROM movimentos
                WHERE {$where}
                GROUP BY accountId";
        $retval = [];
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param(str_repeat('s', sizeof($param_array)), ...$param_array);
        $stmt->execute();
        $stmt->bind_result($income, $expense, $balance);
        $stmt->fetch();
        $retval = [
            'income' => null === $income ? 0.0 : $income,
            'expense' => null === $expense ? 0.0 : $expense,
            'balance' => null === $balance ? 0.0 : $balance
        ];
        $stmt->close();
        return $retval;
    }
    public function update(): bool
    {
        $retval = false;
        $sql = "INSERT INTO {$this->tableName()}
                        (`number`, `name`, `grupo`, `typeId`, `iban`, `swift`, `openDate`, `closeDate`, `active`, `id`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            `number`=VALUES(`number`),
                            `name`=VALUES(`name`),
                            `grupo`=VALUES(`grupo`),
                            `typeId`=VALUES(`typeId`),
                            `iban`=VALUES(`iban`),
                            `swift`=VALUES(`swift`),
                            `openDate`=VALUES(`openDate`),
                            `closeDate`=VALUES(`closeDate`),
                            `active`=VALUES(`active`)";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param(
            "ssiissssii",
            $this->number,
            $this->name,
            $this->grupo,
            $this->typeId,
            $this->iban,
            $this->swift,
            $this->openDate,
            $this->closeDate,
            $this->active,
            $this->id
        );
        $retval = $stmt->execute();
        $stmt->close();
        return $retval;
    }
}
