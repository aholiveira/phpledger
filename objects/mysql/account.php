<?php

/**
 * Holds a mysql-backed `account` object
 * @property string $name The name of the account
 * @property string $number The account's number
 * @property string $iban International Bank Account Number
 * @property string $swift The account's switft identifier
 * @property int $group Links the account to the group table. Used to group different accounts under
 * @property int $type_id Account type - linked to the account_type table
 * @property string $open_date The date the account was open in Y-m-d format
 * @property string $close_date The date the account was closed in Y-m-d format
 * @property int $active Flag to indicate if the account is still active or not
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class account extends mysql_object implements iObject
{
    public string $name = "";
    public string $number = "";
    public string $iban = "";
    public string $swift = "";
    public int $group;
    public int $type_id;
    public string $open_date;
    public string $close_date;
    public int $active;
    protected static string $tableName = "contas";

    public static function getDefinition(): array
    {
        $retval = [];
        $retval['columns'] = [
            "conta_id" => "int(3) NOT NULL DEFAULT 0",
            "conta_num" => "char(30) NOT NULL DEFAULT ''",
            "conta_nome" => "char(30) NOT NULL DEFAULT ''",
            "grupo" => "int(3) NOT NULL DEFAULT 0",
            "tipo_id" => "int(2) DEFAULT NULL",
            "conta_nib" => "char(24) DEFAULT NULL",
            "swift" => "char(24) NOT NULL DEFAULT ''",
            "conta_abertura" => "date DEFAULT NULL",
            "conta_fecho" => "date DEFAULT NULL",
            "activa" => "int(1) NOT NULL DEFAULT 0"
        ];
        $retval['primary_key'] = "conta_id";
        $retval['new'] = [
            'id' => 'conta_id',
            'number' => 'conta_num',
            'name' => 'conta_nome',
            'group' => 'grupo',
            'type_id' => 'tipo_id',
            'iban' => 'conta_nib',
            'open_date' => 'conta_abertura',
            'close_date' => 'conta_fecho',
            'active' => 'activa'
        ];
        return $retval;
    }
    public static function getList(array $field_filter = []): array
    {
        $where = parent::getWhereFromArray($field_filter);
        $sql = "SELECT
            conta_id as id,
            conta_num as `number`,
            conta_nome as `name`,
            grupo as `group`,
            tipo_id as `type_id`,
            conta_nib as iban,
            swift,
            conta_abertura as open_date,
            conta_fecho as close_date,
            activa as active
        FROM " . static::$tableName . "
        {$where}
        ORDER BY activa DESC, conta_nome";
        $retval = [];
        try {
            $stmt = static::$_dblink->prepare($sql);
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

    public static function getById($id): account
    {
        $sql = "SELECT
            conta_id as id,
            conta_num as `number`,
            conta_nome as `name`,
            grupo as `group`,
            tipo_id as `type_id`,
            conta_nib as iban,
            swift,
            conta_abertura as open_date,
            conta_fecho as close_date,
            activa as active
        FROM " . static::tableName() . "
        WHERE conta_id=?";
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
                $retval = new account(static::$_dblink);
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
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
        $where = "account_id=? ";
        $retval = ['income' => 0, 'expense' => 0, 'balance' => 0];
        $param_array = [$this->id];
        if (null !== $startDate) {
            $where .= " AND `entry_date`>=? ";
            $param_array[] = $startDate->format("Y-m-d");
        }
        if (null !== $endDate) {
            $where .= " AND entry_date<=? ";
            $param_array[] = $endDate->format("Y-m-d");
        }
        $sql = "SELECT
                SUM(ROUND(IF(direction='1',euro_amount,0),2)) AS income,
                SUM(ROUND(IF(direction='-1',-euro_amount,0),2)) AS expense,
                ROUND(SUM(ROUND(IF(NOT ISNULL(euro_amount),euro_amount,0),5)),2) AS balance
                FROM movimentos
                WHERE {$where}
                GROUP BY account_id";
        $retval = [];
        try {
            $stmt = @static::$_dblink->prepare($sql);
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
                        (`conta_num`, `conta_nome`, `tipo_id`, `conta_nib`, `swift`, `conta_abertura`, `conta_fecho`, `activa`, `conta_id`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            `conta_num`=VALUES(`conta_num`),
                            `conta_nome`=VALUES(`conta_nome`),
                            `tipo_id`=VALUES(`tipo_id`),
                            `conta_nib`=VALUES(`conta_nib`),
                            `swift`=VALUES(`swift`),
                            `conta_abertura`=VALUES(`conta_abertura`),
                            `conta_fecho`=VALUES(`conta_fecho`),
                            `activa`=VALUES(`activa`)";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "ssissssii",
                $this->number,
                $this->name,
                $this->type_id,
                $this->iban,
                $this->swift,
                $this->open_date,
                $this->close_date,
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
            $stmt = static::$_dblink->prepare($sql);
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
        return parent::getNextId($field);
    }
}
