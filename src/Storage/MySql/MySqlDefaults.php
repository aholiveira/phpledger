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
    use MySqlSelectTrait;
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
    }
    protected static string $tableName = "defaults";

    public function __construct($data = null)
    {
        $this->traitConstruct();
        if ($data === null) {
            $data = [];
        }
        $this->id = $data["id"] ?? 1;
        $this->categoryId = $data["categoryId"] ?? 990;
        $this->accountId = $data["accountId"] ?? 0;
        $this->currencyId = $data["currencyId"] ?? "EUR";
        $this->entryDate = $data["entryDate"] ?? date("Y-m-d");
        $this->direction = $data["direction"] ?? 1;
        $this->language = $data["language"] ?? 'pt-PT';
        $this->lastVisited = $data["lastVisited"] ?? "";
        $this->showReportGraph = $data["showReportGraph"] ?? 0;
        $this->username = $data["username"] ?? Config::get("admin_username");
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
        $sql = self::getSelect() . " {$where} ORDER BY id";
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            if ($stmt->execute() === false) {
                throw new \mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            if ($result === false) {
                throw new \mysqli_sql_exception();
            }
            while ($data = $result->fetch_assoc()) {
                $newobject = new self($data);
                $retval[$newobject->id] = $newobject;
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        } finally {
            if (isset($stmt) && $stmt instanceof \mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    public static function getById(int $id): ?Defaults
    {
        $sql = self::getSelect() . " WHERE id=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            if ($stmt->execute() === false) {
                throw new \mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            if ($result === false) {
                throw new \mysqli_sql_exception();
            }
            $row = $result ? $result->fetch_assoc() : null;
            if (!$row) {
                return null;
            }
            $retval = new self($row);
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        } finally {
            if (isset($stmt) && $stmt instanceof \mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    public static function getByUsername(string $username): ?Defaults
    {
        if (empty($username)) {
            return null;
        }
        $sql = self::getSelect() . " WHERE username COLLATE utf8mb4_general_ci = trim(?)";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $username);
            if ($stmt->execute() === false) {
                throw new \mysqli_sql_exception();
            }
            $result = $stmt->get_result();
            if ($result === false) {
                throw new \mysqli_sql_exception();
            }
            $row = $result->fetch_assoc();
            if (!$row) {
                return null;
            }
            $retval = new self($row);
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        } finally {
            if (isset($stmt) && $stmt instanceof \mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    /**
     * Set values to the initial values
     * Use if there are no persisted defaults in the database
     */
    public static function init(): Defaults
    {
        return new MySqlDefaults();
    }
    public function update(): bool
    {
        $retval = false;
        try {
            $sql = "INSERT INTO {$this->tableName()}
                    (categoryId, accountId, currencyId, entryDate, direction, language, lastVisited, showReportGraph, username, id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    categoryId=VALUES(categoryId),
                    accountId=VALUES(accountId),
                    currencyId=VALUES(currencyId),
                    entryDate=VALUES(entryDate),
                    direction=VALUES(direction),
                    language=VALUES(language),
                    lastVisited=VALUES(lastVisited),
                    showReportGraph=VALUES(showReportGraph),
                    username=VALUES(username)";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param(
                "iisssssisi",
                $this->categoryId,
                $this->accountId,
                $this->currencyId,
                $this->entryDate,
                $this->direction,
                $this->language,
                $this->lastVisited,
                $this->showReportGraph,
                $this->username,
                $this->id
            );
            $retval = $stmt->execute();
            if (false === $retval) {
                throw new \mysqli_sql_exception();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
            if ($retval === false) {
                throw new \mysqli_sql_exception();
            }
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        } finally {
            if (isset($stmt) && $stmt instanceof \mysqli_stmt) {
                $stmt->close();
            }
            if (isset($result) && $result instanceof \mysqli_result) {
                $result->close();
            }
        }
        return $retval;
    }
    public function delete(): bool
    {
        return false;
    }
}
