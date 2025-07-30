<?php

/**
 * Defaults class
 * Holds the object for default values for forms
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class defaults extends mysql_object implements iobject
{
    public int $category_id;
    public int $account_id;
    public string $currency_id;
    public string $entry_date;
    public int $direction;
    public ?string $username;
    protected static string $tableName = "defaults";

    public function __construct(mysqli $dblink, $data = null)
    {
        parent::__construct($dblink);
        $this->id = $data["id"] ?? 1;
        $this->category_id = $data["category_id"] ?? 990;
        $this->account_id = $data["account_id"] ?? 0;
        $this->currency_id = $data["currency_id"] ?? "EUR";
        $this->entry_date = $data["entry_date"] ?? date("Y-m-d");
        $this->direction = $data["direction"] ?? 1;
        $this->username = $data["username"] ?? config::get("admin_username");
    }
    public static function getList(array $field_filter = array()): array
    {
        $where = parent::getWhereFromArray($field_filter);
        $sql = "SELECT
            id as id,
            tipo_mov as `category_id`,
            conta_id as `account_id`,
            moeda_mov as `currency_id`,
            `data` as `entry_date`,
            deb_cred as direction,
            username
        FROM " . defaults::$tableName . "
        {$where}
        ORDER BY id";
        $retval = [];
        try {
            if (!is_object(static::$_dblink))
                return $retval;
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new mysqli_sql_exception(static::$_dblink->error);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getById($id): ?defaults
    {
        $sql = "SELECT
            id,
            tipo_mov as `category_id`,
            conta_id as `account_id`,
            moeda_mov as `currency_id`,
            `data` as `entry_date`,
            deb_cred as direction,
            username
            FROM " . defaults::$tableName . "
            WHERE id=?";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new mysqli_sql_exception();
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if (!$stmt)
                throw new mysqli_sql_exception();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getByUsername(string $username): ?defaults
    {
        $sql = "SELECT
            id,
            tipo_mov as `category_id`,
            conta_id as `account_id`,
            moeda_mov as `currency_id`,
            `data` as `entry_date`,
            deb_cred as direction,
            username
            FROM " . defaults::$tableName . "
            WHERE trim(lower(username))=trim(lower(?))";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                throw new mysqli_sql_exception();
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if (!$stmt)
                throw new mysqli_sql_exception();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $retval = new defaults(static::$_dblink, $row);
            }
            $stmt->close();
        } catch (Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    /**
     * Set values to the initial values
     * Use if there are no persisted defaults in the database
     */
    public static function init(): defaults
    {
        return new defaults(static::$_dblink);
    }
    public function update(): bool
    {
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        $retval = false;
        try {
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false)
                return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            null !== $stmt->fetch() && $return_id == $this->id ?
                $sql = "UPDATE {$this->tableName()} SET
                    tipo_mov=?,
                    conta_id=?,
                    moeda_mov=?,
                    `data`=?,
                    deb_cred=?,
                    username=?
                    WHERE id=?"
                :
                $sql = "INSERT INTO {$this->tableName()} (tipo_mov, conta_id, moeda_mov, `data`, deb_cred, username, id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if (!$stmt)
                throw new \mysqli_sql_exception(static::$_dblink->error);
            $stmt->bind_param(
                "ssssssi",
                $this->category_id,
                $this->account_id,
                $this->currency_id,
                $this->entry_date,
                $this->direction,
                $this->username,
                $this->id
            );
            if (!$stmt)
                throw new \mysqli_sql_exception(static::$_dblink->error);
            $retval = $stmt->execute();
            $stmt->close();
            if (!$retval)
                throw new \mysqli_sql_exception(static::$_dblink->error);
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            static::$_dblink->rollback();
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        return false;
    }
}
