<?php

/**
 * User class
 * Handles user registration and authentication
 * 
 * @since 0.2.0
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 */
define("USER_ROLE_ADM", 255);
define("USER_ROLE_RW", 192);
define("USER_ROLE_RO", 128);
class user extends mysql_object implements iobject
{
    protected string $_username;
    protected string $_password;
    protected string $_email = '';
    protected string $_fullname = '';
    protected string $_token = '';
    protected ?string $_token_expiry;
    protected int $_active;
    protected int $_role;
    protected static string $tableName = "users";
    protected static int $_token_length = 32;

    public function __construct(\mysqli $dblink)
    {
        parent::__construct($dblink);
        $this->_token_expiry = null;
    }
    public function setUsername(string $value)
    {
        $this->_username = $value;
    }
    public function getUsername(): ?string
    {
        return isset($this->_username) ? $this->_username : null;
    }
    public function setPassword(string $value)
    {
        $this->_password = $this->hashPassword($value);
    }
    /**
     * This returns the password hash. 
     * The unhashed password is never stored on the object
     * @return string the hashed value of the password
     */
    public function getPassword(): ?string
    {
        return isset($this->_password) ? $this->_password : null;
    }
    public function setEmail(string $value)
    {
        $this->_email = $value;
    }
    public function getEmail(): ?string
    {
        return isset($this->_email) ? $this->_email : null;
    }
    public function setFullName(string $value)
    {
        $this->_fullname = $value;
    }
    public function getFullName(): ?string
    {
        return isset($this->_fullname) ? $this->_fullname : null;
    }
    public function setActive(int $value)
    {
        $this->_active = $value;
    }
    public function getActive(): ?int
    {
        return isset($this->_active) ? $this->_active : null;
    }
    public function setRole(int $value)
    {
        $this->_role = $value;
    }
    public function getRole(): ?int
    {
        return isset($this->_role) ? $this->_role : null;
    }
    public function getToken(): ?string
    {
        return isset($this->_token) ? $this->_token : null;
    }
    public function setToken(string $value)
    {
        $this->_token = $value;
    }
    public function getTokenExpiry(): ?string
    {
        return isset($this->_token_expiry) ? $this->_token_expiry : null;
    }
    public function setTokenExpiry(?string $value)
    {
        $this->_token_expiry = $value;
    }
    private function hashPassword(string $password): string
    {
        if (function_exists('sodium_crypto_pwhash_str')) {
            return sodium_crypto_pwhash_str($password, SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
        } else {
            return password_hash($password, PASSWORD_DEFAULT);
        }
    }
    public function verifyPassword(string $password): bool
    {
        if (empty($password)) return FALSE;
        if (function_exists('sodium_crypto_pwhash_str_verify')) {
            return sodium_crypto_pwhash_str_verify($this->getPassword(), $password);
        } else {
            return password_verify($password, $this->getPassword());
        }
    }
    public function createToken(): string
    {
        return bin2hex(random_bytes(user::$_token_length));
    }
    public function isTokenValid(string $token): bool
    {
        return (date("Y-m-d HH:ii:ss") <= $this->_token_expiry && $this->_token == $token);
    }
    public function update(): bool
    {
        $retval = false;
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            //static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "UPDATE {$this->tableName()} SET 
                    `username`=?, 
                    `password`=?, 
                    `fullname`=?, 
                    `email`=?, 
                    `role`=?,
                    `token`=?,
                    `token_expiry`=?,
                    `active`=?
                    WHERE `id`=?";
            } else {
                $sql = "INSERT INTO {$this->tableName()} (username, password, fullname, email, role, token, token_expiry, active, id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            }
            $stmt->close();
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            if (!is_null($this->_token_expiry) && strlen($this->_token_expiry) == 0) {
                $this->_token_expiry = NULL;
            }
            $stmt->bind_param(
                "sssssssss",
                $this->_username,
                $this->_password,
                $this->_fullname,
                $this->_email,
                $this->_role,
                $this->_token,
                $this->_token_expiry,
                $this->_active,
                $this->id
            );
            $retval = $stmt->execute();
            if ($retval == false) {
                throw new mysqli_sql_exception(static::$_dblink->error);
            }
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
            if (isset($stmt)) $stmt->close();
            static::$_dblink->rollback();
        }
        return $retval;
    }
    public static function getList(array $field_filter = array()): array
    {
        $where = parent::getWhereFromArray($field_filter);
        $sql = "SELECT id, 
            username AS `_username`, 
            `password` AS `_password`, 
            `fullname` AS `_fullname`, 
            email AS `_email`, 
            `role` AS `_role`, 
            `token` AS `_token`, 
            `token_expiry` AS `_token_expiry`, 
            active AS `_active` 
            FROM " . static::tableName() . " 
            {$where} 
            ORDER BY username";
        $retval = array();
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception(static::$_dblink->error);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($newobject = $result->fetch_object(__CLASS__, array(static::$_dblink))) {
                $retval[$newobject->id] = $newobject;
            }
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public static function getByUsername(string $username): ?user
    {
        $sql = "SELECT id, 
            username AS `_username`, 
            `password` AS `_password`, 
            `fullname` AS `_fullname`, 
            email AS `_email`, 
            `role` AS `_role`, 
            `token` AS `_token`, 
            `token_expiry` AS `_token_expiry`, 
            active AS `_active` 
            FROM " . static::tableName() . "
            WHERE username=?";
        $retval = null;
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $newobject = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $newobject;
    }
    public static function getById(int $id): ?user
    {
        $sql = "SELECT id, 
        `username` AS `_username`, 
        `password` AS `_password`, 
        `fullname` AS `_fullname`, 
        `email` AS `_email`, 
        `role` AS `_role`, 
        `token` AS `_token`, 
        `token_expiry` AS `_token_expiry`, 
        `active` AS `_active` 
        FROM " . static::tableName() . "
        WHERE id=?";
        $retval = null;
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function resetPassword(): bool
    {
        $retval = false;
        if (!isset($this->_username) || !isset($this->_email)) {
            return $retval;
        }
        $this->setToken($this->createToken());
        $this->setTokenExpiry((new \DateTime(date("Y-m-d H:i:s")))->add(new \DateInterval("PT24H"))->format("Y-m-d H:i:s"));
        if ($this->update()) {
            $retval = true;
            $title = config::get("title");
            $url = config::get("url");
            $message = "Esta' a receber este email porque solicitou a reposicao da sua palavra-passe na aplicacao '$title'.\r\n";
            $message .= "Para continuar o processo deve clique no link abaixo para definir uma nova senha.\r\n";
            $message .= "{$url}reset_password.php?token_id={$this->getToken()}.\r\n";
            $message .= "Este token e' valido ate' 'as {$this->getTokenExpiry()}.\r\n";
            $message .= "Findo este prazo tera' que reiniciar o processo usando o link {$url}forgot_password.php.\r\n";
            $message .= "\r\n";
            $message .= "Cumprimentos,\r\n";
            $message .= "$title\r\n";
            $retval = Email::send_email(config::get("from"), $this->getEmail(), "Reposicao de palavra-passe", $message);
        }
        return $retval;
    }
    public static function getByToken(string $token): ?user
    {
        $sql = "SELECT id, 
        `username` AS `_username`, 
        `password` AS `_password`, 
        `fullname` AS `_fullname`, 
        `email` AS `_email`, 
        `role` AS `_role`, 
        `token` AS `_token`, 
        `token_expiry` AS `_token_expiry`, 
        `active` AS `_active` 
        FROM " . static::tableName() . "
        WHERE token=?";
        $retval = null;
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $retval = $result->fetch_object(__CLASS__, array(static::$_dblink));
            $stmt->close();
        } catch (\Exception $ex) {
            static::handleException($ex, $sql);
        }
        return $retval;
    }
    public function delete(): bool
    {
        $retval = false;
        $sql = "SELECT id FROM {$this->tableName()} WHERE id=?";
        try {
            if (!(static::$_dblink->ping())) {
                return $retval;
            }
            static::$_dblink->begin_transaction();
            $stmt = @static::$_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            if (!isset($this->id)) return $retval;
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($return_id);
            if (!is_null($stmt->fetch()) && $return_id == $this->id) {
                $sql = "DELETE FROM {$this->tableName()} WHERE `id`=?";
            }
            $stmt->close();
            if (empty($return_id)) {
                return true;
            }
            $stmt = static::$_dblink->prepare($sql);
            if ($stmt == false) throw new \mysqli_sql_exception("Error on function " . __FUNCTION__ . " class " . __CLASS__);
            $stmt->bind_param("s", $this->id);
            $retval = $stmt->execute();
            if ($retval == false) {
                throw new mysqli_sql_exception(static::$_dblink->error);
            }
            static::$_dblink->commit();
        } catch (\Exception $ex) {
            $this->handleException($ex, $sql);
            if (isset($stmt)) $stmt->close();
            static::$_dblink->rollback();
        }
        return $retval;
    }
}
