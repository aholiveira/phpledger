<?php
namespace PHPLedger\Domain;

use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Storage\Abstract\AbstractDataObject;
use \PHPLedger\Util\Config;
use \PHPLedger\Util\Email;
const USER_ROLE_ADM = 255;
const USER_ROLE_RW = 192;
const USER_ROLE_RO = 128;
abstract class User extends AbstractDataObject implements DataObjectInterface
{
    protected string $_username;
    protected string $_password;
    protected string $_email = '';
    protected string $_fullname = '';
    protected string $_token = '';
    protected ?string $_token_expiry;
    protected int $_active;
    protected int $_role;
    protected static int $_token_length = 32;
    public function setUsername(string $value)
    {
        $this->_username = $value;
    }
    public function getUsername(): ?string
    {
        return $this->_username;
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
        return $this->_password;
    }
    public function setEmail(string $value)
    {
        $this->_email = $value;
    }
    public function getEmail(): ?string
    {
        return $this->_email;
    }
    public function setFullName(string $value)
    {
        $this->_fullname = $value;
    }
    public function getFullName(): ?string
    {
        return $this->_fullname;
    }
    public function setActive(int $value)
    {
        $this->_active = $value;
    }
    public function getActive(): ?int
    {
        return $this->_active;
    }
    public function setRole(int $value)
    {
        $this->_role = $value;
    }
    public function getRole(): ?int
    {
        return $this->_role;
    }
    public function getToken(): ?string
    {
        return $this->_token;
    }
    public function setToken(string $value)
    {
        $this->_token = $value;
    }
    public function getTokenExpiry(): ?string
    {
        return $this->_token_expiry;
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
        if (empty($password)) {
            return false;
        }
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
        return date("Y-m-d H:i:s") <= $this->_token_expiry && $this->_token == $token;
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
            $title = Config::get("title");
            $url = Config::get("url");
            $message = "Esta' a receber este email porque solicitou a reposicao da sua palavra-passe na aplicacao '$title'.\r\n";
            $message .= "Para continuar o processo deve clique no link abaixo para definir uma nova senha.\r\n";
            $message .= "{$url}reset_password.php?token_id={$this->getToken()}.\r\n";
            $message .= "Este token e' valido ate' 'as {$this->getTokenExpiry()}.\r\n";
            $message .= "Findo este prazo tera' que reiniciar o processo usando o link {$url}forgot_password.php.\r\n";
            $message .= "\r\n";
            $message .= "Cumprimentos,\r\n";
            $message .= "$title\r\n";
            $retval = Email::sendEmail(config::get("from"), $this->getEmail(), "Reposicao de palavra-passe", $message);
        }
        return $retval;
    }
    abstract public static function getByUsername(string $username): ?User;

    abstract public static function getByToken(string $token): ?user;

}
