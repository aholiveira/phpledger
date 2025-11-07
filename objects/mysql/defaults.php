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

class Defaults extends MySqlObject implements iObject
{
    public int $category_id;
    public int $account_id;
    public string $currency_id;
    public string $entry_date;
    public int $direction;
    public ?string $language;
    public ?string $username;
    public ?string $last_visited;
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
        $this->language = $data["language"] ?? 'pt-PT';
        $this->last_visited = $data["last_visited"] ?? "";
        $this->username = $data["username"] ?? config::get("admin_username");
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [];
        $retval['columns'] = [
            "id" => "int(1) NOT NULL DEFAULT 0",
            "tipo_mov" => "int(3) DEFAULT NULL",
            "conta_id" => "int(3) DEFAULT NULL",
            "moeda_mov" => "char(3) DEFAULT NULL",
            "data" => "date DEFAULT NULL",
            "deb_cred" => "enum('1','-1') DEFAULT NULL",
            "language" => "char(10) DEFAULT NULL",
            "last_visited" => "char(255) DEFAULT NULL",
            "username" => "char(100) DEFAULT NULL",
            "show_report_graph" => "int(1) NOT NULL DEFAULT 0",
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    public static function getList(array $field_filter = []): array
    {
        $where = parent::getWhereFromArray($field_filter);
        $sql = "SELECT
            id as id,
            tipo_mov as `category_id`,
            conta_id as `account_id`,
            moeda_mov as `currency_id`,
            `data` as `entry_date`,
            deb_cred as direction,
            `language`,
            last_visited,
            username
        FROM " . defaults::$tableName . "
        {$where}
        ORDER BY id";
        $retval = [];
        try {
            if (!is_object(static::$_dblink)) {
                return $retval;
            }
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, [static::$_dblink])) {
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
            `language`,
            last_visited,
            username
            FROM " . defaults::$tableName . "
            WHERE id=?";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if (!$stmt) {
                throw new mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, [static::$_dblink]);
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
            `language`,
            last_visited,
            username
            FROM " . defaults::$tableName . "
            WHERE trim(lower(username))=trim(lower(?))";
        $retval = null;
        try {
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if (!$stmt) {
                throw new mysqli_sql_exception();
            }
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
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                    (tipo_mov, conta_id, moeda_mov, `data`, deb_cred, `language`, last_visited, username, id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    tipo_mov=VALUES(tipo_mov),
                    conta_id=VALUES(conta_id),
                    moeda_mov=VALUES(moeda_mov),
                    `data`=VALUES(`data`),
                    deb_cred=VALUES(deb_cred),
                    `language`=VALUES(`language`),
                    last_visited=VALUES(last_visited),
                    username=VALUES(username)";
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "ssssssssi",
                $this->category_id,
                $this->account_id,
                $this->currency_id,
                $this->entry_date,
                $this->direction,
                $this->language,
                $this->last_visited,
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
