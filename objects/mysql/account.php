<?php

/**
 * Account class
 * Holds an account object
 * @property string name The name of the account
 * @property string number The account's number
 * @property string iban International Bank Account Number
 * @property string swift The account's switft identifier
 * @property int group Links the account to the group table. Used to group different accounts under
 * @property int type_id Account type - linked to the account_type table
 * @property string open_date The date the account was open in Y-m-d format
 * @property string close_date The date the account was closed in Y-m-d format
 * @property int active Flag to indicate if the account is still active or not
 * 
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class account extends mysql_object implements iobject
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

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
    }
    public function getAll(array $field_filter = array()): array
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
        FROM {$this->tableName()}
        {$where}
        ORDER BY activa DESC, conta_nome";
        $retval = array();
        try {
            if (!is_object(static::$_dblink)) return $retval;
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception(static::$_dblink->error);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }

    public function getById($id): account
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
        FROM {$this->tableName()}
        WHERE conta_id=?";
        try {
            if (is_object(static::$_dblink)) {
                $stmt = @static::$_dblink->prepare($sql);
                if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
                $stmt->close();
                if ($newobject instanceof account) {
                    $this->copyfromObject($newobject);
                }
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $this;
    }
    /**
     * @return array array with keys 'income', 'expense' and 'balance'
     * representing the corresponding amounts (euro-based) for that account and
     * on or before the reference date
     */
    public function getBalanceOnDate(\DateTime $date): array
    {
        return $this->getBalance(null, $date);
    }
    public function getBalance(\DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $where = "WHERE conta_id=? ";
        $param_array = array($this->id);
        if (!is_null($startDate)) {
            $where .= " AND `data_mov`>=? ";
            $param_array[] = $startDate->format("Y-m-d");
        }
        if (!is_null($endDate)) {
            $where .= " AND data_mov<=? ";
            $param_array[] = $endDate->format("Y-m-d");
        }
        $sql = "SELECT 
        SUM(ROUND(IF(deb_cred='1',valor_euro,0),2)) AS income, 
        SUM(ROUND(IF(deb_cred='-1',-valor_euro,0),2)) AS expense, 
        ROUND(SUM(ROUND(IF(NOT ISNULL(valor_euro),valor_euro,0),5)),2) AS balance 
        FROM movimentos 
        {$where} 
        GROUP BY conta_id ";
        $retval = array();
        try {
            if (is_object(static::$_dblink)) {
                $stmt = @static::$_dblink->prepare($sql);
                if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
                $stmt->bind_param(str_repeat('s', sizeof($param_array)), ...$param_array);
                $stmt->execute();
                $stmt->bind_result($income, $expense, $balance);
                $stmt->fetch();
                $retval = array('income' => $income, 'expense' => $expense, 'balance' => $balance);
                $stmt->close();
            }
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function save(): bool
    {
        $retval = false;
        if (!is_object(static::$_dblink)) return $retval;
        $sql = "SELECT conta_id FROM {$this->tableName()} WHERE conta_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET 
                    `conta_num`=?,
                    `conta_nome`=?,
                    `tipo_id`=?,
                    `conta_nib`=?,
                    `swift`=?,
                    `conta_abertura`=?,
                    `conta_fecho`=?,
                    `activa`=?
                    WHERE `conta_id`=?";
            } else {
                $sql = "INSERT INTO " . static::$tableName
                    . "(`conta_num`, `conta_nome`, `tipo_id`, `conta_nib`, `swift`, `conta_abertura`, `conta_fecho`, `activa`, `conta_id`) "
                    . "VALUES "
                    . "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            $stmt->bind_param(
                "sssssssss",
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
            if (!$retval) throw new \mysqli_sql_exception(static::$_dblink->error);
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            static::$_dblink->rollback();
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        $retval = false;
        if (!is_object(static::$_dblink)) return $retval;
        $sql = "SELECT conta_id FROM {$this->tableName()} WHERE conta_id=?";
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (is_null($stmt->fetch())) {
                return $retval;
            }
            $stmt->close();
            $sql = "DELETE FROM {$this->tableName()} WHERE conta_id=?";
            $stmt = static::$_dblink->prepare($sql);
            $stmt->bind_param("s", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
            static::$_dblink->commit();
            $this->clear();
        } catch (\Exception $ex) {
            static::$_dblink->rollback();
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function getFreeId(string $field = "conta_id"): int
    {
        return parent::getFreeId($field);
    }
}
