<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Storage\MySql;
use PHPLedger\Domain\Defaults;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;
class MySqlDefaults extends Defaults
{
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
    }
    protected static string $tableName = "defaults";

    public function __construct($data = null)
    {
        $this->traitConstruct();
        if (!empty($data) && \is_array($data)) {
            $this->id = $data["id"] ?? 1;
            $this->categoryId = $data["categoryId"] ?? 990;
            $this->accountId = $data["accountId"] ?? 0;
            $this->currencyId = $data["currencyId"] ?? "EUR";
            $this->entryDate = $data["entryDate"] ?? date("Y-m-d");
            $this->direction = $data["direction"] ?? 1;
            $this->language = $data["language"] ?? 'pt-PT';
            $this->lastVisited = $data["lastVisited"] ?? "";
            $this->showReportGraph = $data["showReportGraph"] ?? 0;
            $this->username = $data["username"] ?? config::get("admin_username");
        }
    }
    public static function getDefinition(): array
    {
        $retval = [];
        $retval['new'] = [
            "tipo_mov" => "categoryId",
            "conta_id" => "accountId",
            "moeda_mov" => "currencyId",
            "data" => "entryDate",
            "deb_cred" => "direction",
            "last_visited" => "lastVisited",
            "show_report_graph" => "showReportGraph"
        ];
        $retval['columns'] = [
            "id" => "int(1) NOT NULL DEFAULT 0",
            "categoryId" => "int(3) DEFAULT NULL",
            "accountId" => "int(3) DEFAULT NULL",
            "currencyId" => "char(3) DEFAULT NULL",
            "entryDate" => "date DEFAULT NULL",
            "direction" => "enum('1','-1') DEFAULT NULL",
            "language" => "char(10) DEFAULT NULL",
            "lastVisited" => "char(255) DEFAULT NULL",
            "username" => "char(100) DEFAULT NULL",
            "showReportGraph" => "int(1) NOT NULL DEFAULT 0",
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = "SELECT
            id,
            `categoryId`,
            `accountId`,
            `currencyId`,
            `entryDate`,
            direction,
            `language`,
            lastVisited,
            username
        FROM " . self::$tableName . "
        {$where}
        ORDER BY id";
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
    public static function getById($id): ?Defaults
    {
        $sql = "SELECT
            id,
            `categoryId`,
            `accountId`,
            `currencyId`,
            `entryDate`,
            direction,
            `language`,
            lastVisited,
            username
            FROM " . self::$tableName . "
            WHERE id=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if (!$stmt) {
                throw new \mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getByUsername(string $username): ?defaults
    {
        $sql = "SELECT
            id,
            `categoryId`,
            `accountId`,
            `currencyId`,
            `entryDate`,
            direction,
            `language`,
            lastVisited,
            username
            FROM " . self::$tableName . "
            WHERE trim(lower(username))=trim(lower(?))";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if (!$stmt) {
                throw new \mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
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
        return new MySqlDefaults();
    }
    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                    (categoryId, accountId, currencyId, `entryDate`, direction, `language`, lastVisited, username, id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    categoryId=VALUES(categoryId),
                    accountId=VALUES(accountId),
                    currencyId=VALUES(currencyId),
                    `entryDate`=VALUES(`entryDate`),
                    direction=VALUES(direction),
                    `language`=VALUES(`language`),
                    lastVisited=VALUES(lastVisited),
                    username=VALUES(username)";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "ssssssssi",
                $this->categoryId,
                $this->accountId,
                $this->currencyId,
                $this->entryDate,
                $this->direction,
                $this->language,
                $this->lastVisited,
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
