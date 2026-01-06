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
use PHPLedger\Storage\MySql\Traits\MySqlSelectTrait;
use PHPLedger\Services\Config;

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
        $this->lastVisitedUri = $data["lastVisitedUri"] ?? "";
        $this->lastVisitedAt = $data["lastVisitedAt"] ?? time();
        $this->showReportGraph = $data["showReportGraph"] ?? 0;
        $this->username = $data["username"] ?? Config::instance()->get("admin_username");
    }
    public static function getDefinition(): array
    {
        return [
            "id",
            "categoryId",
            "accountId",
            "currencyId",
            "entryDate",
            "direction",
            "language",
            "lastVisitedUri",
            "lastVisitedAt",
            "username",
            "showReportGraph",
        ];
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = self::getSelect() . " {$where} ORDER BY id";
        $retval = [];
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($data = $result->fetch_assoc()) {
                $newobject = new self($data);
                $retval[$newobject->id] = $newobject;
            }
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
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if (!$row) {
                return null;
            }
            $retval = new self($row);
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
    public static function getByUsername(string $username): ?self
    {
        if (empty($username)) {
            return null;
        }
        $sql = self::getSelect() . " WHERE username COLLATE utf8mb4_general_ci = trim(?)";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (!$row) {
                return null;
            }
            $retval = new self($row);
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
    public static function init(): self
    {
        return new MySqlDefaults();
    }
    public function update(): bool
    {
        $sql = "INSERT INTO {$this->tableName()}
                    (id, categoryId, accountId, currencyId, entryDate, direction, language, lastVisitedUri, lastVisitedAt, showReportGraph, username)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    categoryId=VALUES(categoryId),
                    accountId=VALUES(accountId),
                    currencyId=VALUES(currencyId),
                    entryDate=VALUES(entryDate),
                    direction=VALUES(direction),
                    language=VALUES(language),
                    lastVisitedUri=VALUES(lastVisitedUri),
                    lastVisitedAt=VALUES(lastVisitedAt),
                    showReportGraph=VALUES(showReportGraph),
                    username=VALUES(username)";
        $typeString = self::buildTypesString([
            $this->categoryId,
            $this->accountId,
            $this->currencyId,
            $this->entryDate,
            $this->language,
            "'" . $this->direction . "'",
            $this->lastVisitedUri,
            $this->lastVisitedAt,
            $this->showReportGraph,
            $this->username
        ]);
        return $this->saveWithTransaction(
            $sql,
            $typeString,
            [
                $this->categoryId,
                $this->accountId,
                $this->currencyId,
                $this->entryDate,
                $this->direction,
                $this->language,
                $this->lastVisitedUri,
                $this->lastVisitedAt,
                $this->showReportGraph,
                $this->username,
            ]
        );
    }
    private static function buildTypesString(array $fields): string
    {
        $retval = "";
        foreach ($fields as $field) {
            switch (gettype($field)) {
                case "integer":
                    $retval .= "i";
                    break;
                case "double":
                    $retval .= "d";
                    break;
                case "string":
                    $retval .= "s";
                    break;
                default:
                    $retval .= "b";
                    break;
            }
        }
        return $retval;
    }
    public function delete(): bool
    {
        return false;
    }
}
