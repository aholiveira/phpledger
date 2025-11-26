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
        $notNull = "NOT NULL";
        $defaultEmpty = "DEFAULT ''";
        $char255 = "char(255)";
        $retval = [];
        $retval['new'] = [
            'token_expiry' => 'tokenExpiry'
        ];
        $retval['columns'] = [
            "id" => "int(3) $notNull DEFAULT 0",
            "username" => "char(100) $notNull",
            "password" => "$char255 $notNull",
            "fullName" => "$char255 $notNull $defaultEmpty",
            "email" => "$char255 $notNull $defaultEmpty",
            "role" => "int(3) $notNull DEFAULT 0",
            "token" => "$char255 $notNull $defaultEmpty",
            "tokenExpiry" => "datetime",
            "active" => "int(1) $notNull DEFAULT 0"
        ];
        $retval['primary_key'] = "id";
        return $retval;
    }
    public function update(): bool
    {
        $retval = false;
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if (!$stmt) {
                return $retval;
            }
            if (!isset($this->id)) {
                return $retval;
            }
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            $sql = (null !== $stmt->fetch() && $return_id == $this->id) ?
                "UPDATE {$this->tableName()} SET
                    `username`=?,
                    `password`=?,
                    `fullName`=?,
                    `email`=?,
                    `role`=?,
                    `token`=?,
                    `tokenExpiry`=?,
                    `active`=?
                    WHERE `id`=?"
                :
                "INSERT INTO {$this->tableName()} (username, password, fullName, email, role, token, tokenExpiry, active, id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt->close();
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            if (empty($this->tokenExpiry)) {
                $this->tokenExpiry = null;
            }
            $stmt->bind_param(
                "sssssisii",
                $this->userName,
                $this->password,
                $this->fullName,
                $this->email,
                $this->role,
                $this->token,
                $this->tokenExpiry,
                $this->active,
                $this->id
            );
            $retval = $stmt->execute();
            if ($retval === false) {
                throw new \mysqli_sql_exception();
            }
            MySqlStorage::getConnection()->commit();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $retval;
    }
    public static function getList(array $fieldFilter = []): array
    {
        $where = self::getWhereFromArray($fieldFilter);
        $sql = "SELECT id,
            userName,
            `password`,
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
    public static function getByUsername(string $username): ?User
    {
        $sql = "SELECT id,
            username AS `userName`,
            `password`,
            `fullName`,
            email,
            `role` AS `role`,
            `token`,
            `tokenExpiry`,
            active
            FROM " . static::tableName() . "
            WHERE username=?";
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval instanceof self ? $retval : null;
    }
    public static function getById(int $id): ?user
    {
        $sql = "SELECT id,
        `userName`,
        `password`,
        `fullName`,
        `email`,
        `role`,
        `token`,
        `tokenExpiry`,
        `active`
        FROM " . static::tableName() . "
        WHERE id=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval instanceof self ? $retval : null;
    }
    public static function getByToken(string $token): ?user
    {
        $sql = "SELECT id,
        `userName`,
        `password`,
        `fullName`,
        `email`,
        `role`,
        `token`,
        `tokenExpiry`,
        `active`
        FROM " . static::tableName() . "
        WHERE token=?";
        $retval = null;
        try {
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__);
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
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
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $retval;
    }
}
