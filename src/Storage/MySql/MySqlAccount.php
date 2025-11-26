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
use PHPLedger\Domain\Account;
class MySqlAccount extends Account
{
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
        MySqlObject::getNextId as private traitGetNextId;
    }
    protected static string $tableName = "contas";
    private static function baseSelect(): string
    {
        return "
            SELECT
                conta_id as id,
                `number`,
                `name`,
                grupo as `group`,
                `typeId`,
                conta_nib as iban,
                swift,
                openDate,
                closeDate,
                activa as active
            FROM " . static::$tableName . " ";
    }

    private static function fetchAll(string $sql, array $params = []): array
    {
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            if ($params) {
                $types = str_repeat('s', \count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($obj = $result->fetch_object(__CLASS__)) {
                $retval[$obj->id] = $obj;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }

        return $retval;
    }
    private static function fetchOne(string $sql, array $params = []): Account
    {
        $all = static::fetchAll($sql, $params);
        return array_shift($all) ?: new MySqlAccount();
    }

    public static function getList(array $fieldFilter = []): array
    {
        $where = static::getWhereFromArray($fieldFilter);
        $sql = static::baseSelect() . " {$where} ORDER BY activa DESC, name";
        return static::fetchAll($sql);
    }

    public static function getById($id): Account
    {
        $sql = static::baseSelect() . " WHERE conta_id=?";
        return static::fetchOne($sql, [$id]);
    }

    public static function getDefinition(): array
    {
        $retval = [];
        $retval['columns'] = [
            "conta_id" => "int(3) NOT NULL DEFAULT 0",
            "number" => "char(30) NOT NULL DEFAULT ''",
            "name" => "char(30) NOT NULL DEFAULT ''",
            "grupo" => "int(3) NOT NULL DEFAULT 0",
            "typeId" => "int(2) DEFAULT NULL",
            "conta_nib" => "char(24) DEFAULT NULL",
            "swift" => "char(24) NOT NULL DEFAULT ''",
            "openDate" => "date DEFAULT NULL",
            "closeDate" => "date DEFAULT NULL",
            "activa" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "conta_id";
        $retval['new'] = [
            'id' => 'conta_id',
            'group' => 'grupo',
            'type_id' => 'typeId',
            'iban' => 'conta_nib',
            'open_date' => 'openDate',
            'close_date' => 'closeDate',
            'active' => 'activa',
            'conta_abertura' => 'openDate',
            'conta_fecho' => 'closeDate',
            'conta_num' => 'number',
            'conta_nome' => 'name',
            'tipo_id' => 'typeId'
        ];
        return $retval;
    }
    /**
     * @return array array with keys 'income', 'expense' and 'balance'
     * representing the corresponding amounts (euro-based) for that account and
     * on or before the reference date
     */
    public function getBalanceOnDate(\DateTimeInterface $date): array
    {
        return $this->getBalance(null, $date);
    }
    public function getBalance(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $where = "accountId=? ";
        $retval = ['income' => 0, 'expense' => 0, 'balance' => 0];
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
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
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
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                        (`number`, `name`, `grupo`, `typeId`, `conta_nib`, `swift`, `openDate`, `closeDate`, `activa`, `conta_id`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            `number`=VALUES(`number`),
                            `name`=VALUES(`name`),
                            `grupo`=VALUES(`grupo`),
                            `typeId`=VALUES(`typeId`),
                            `conta_nib`=VALUES(`conta_nib`),
                            `swift`=VALUES(`swift`),
                            `openDate`=VALUES(`openDate`),
                            `closeDate`=VALUES(`closeDate`),
                            `activa`=VALUES(`activa`)";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "ssiissssii",
                $this->number,
                $this->name,
                $this->group,
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
            $sql = "DELETE FROM {$this->tableName()} WHERE conta_id=?";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            $stmt->bind_param("s", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getNextId(string $field = "conta_id"): int
    {
        return self::traitGetNextId($field);
    }
}
