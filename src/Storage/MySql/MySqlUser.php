<?php

/**
 *
 * @since 0.2.0
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 */

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\User;
use Throwable;

class MySqlUser extends User
{
    use MySqlObject {
        MySqlObject::__construct as private traitConstruct;
    }
    protected static string $tableName = "users";
    public function __construct()
    {
        $this->traitConstruct();
        $this->tokenExpiry = null;
    }
    public static function getDefinition(): array
    {
        return [
            "id",
            "username",
            "password",
            "firstName",
            "lastName",
            "fullName",
            "email",
            "role",
            "token",
            "tokenExpiry",
            "active"
        ];
    }
    public function update(): bool
    {
        $sql = "INSERT INTO {$this->tableName()}
        (id, username, password, firstName, lastName, fullName, email, role, token, tokenExpiry, active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            username=VALUES(username),
            password=VALUES(password),
            firstName=VALUES(firstName),
            lastName=VALUES(lastName),
            fullName=VALUES(fullName),
            email=VALUES(email),
            role=VALUES(role),
            token=VALUES(token),
            tokenExpiry=VALUES(tokenExpiry),
            active=VALUES(active)";

        return $this->saveWithTransaction($sql, "ssssssisii", [
            $this->userName,
            $this->password,
            $this->firstName,
            $this->lastName,
            $this->fullName,
            $this->email,
            $this->role,
            $this->token,
            $this->tokenExpiry,
            $this->active
        ]);
    }

    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = "SELECT id,
            userName,
            `password`,
            `firstName`,
            `lastName`,
            `fullName`,
            email,
            `role`,
            `token`,
            `tokenExpiry`,
            `active`
            FROM " . static::tableName() . "
            {$where}
            ORDER BY username";
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
    public static function getByUsername(string $username): ?User
    {
        $sql = "SELECT id,
            username AS `userName`,
            `password`,
            `firstName`,
            `lastName`,
            `fullName`,
            email,
            `role` AS `role`,
            `token`,
            `tokenExpiry`,
            active
            FROM " . static::tableName() . "
            WHERE username=?";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        return $retval instanceof self ? $retval : null;
    }
    public static function getById(int $id): ?user
    {
        $sql = "SELECT id,
        `userName`,
        `password`,
        `firstName`,
        `lastName`,
        `fullName`,
        `email`,
        `role`,
        `token`,
        `tokenExpiry`,
        `active`
        FROM " . static::tableName() . "
        WHERE id=?";
        $retval = null;
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        return $retval instanceof self ? $retval : null;
    }
    public static function getByToken(string $token): ?user
    {
        $sql = "SELECT id,
        `userName`,
        `password`,
        `firstName`,
        `lastName`,
        `fullName`,
        `email`,
        `role`,
        `token`,
        `tokenExpiry`,
        `active`
        FROM " . static::tableName() . "
        WHERE token=?";
        $retval = null;
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $retval = $result->fetch_object(__CLASS__);
        $stmt->close();
        return $retval instanceof self ? $retval : null;
    }
    public function delete(): bool
    {
        $retval = false;
        try {
            $sql = "DELETE FROM {$this->tableName()} WHERE `id`=?";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            $stmt->bind_param("i", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $retval;
    }
}
