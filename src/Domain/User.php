<?php

namespace PHPLedger\Domain;

use DateInterval;
use DateTime;
use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Services\Config;
use PHPLedger\Storage\Abstract\AbstractDataObject;
use PHPLedger\Services\Email;
use PHPLedger\Util\PasswordManager;

abstract class User extends AbstractDataObject implements UserObjectInterface
{
    public const USER_ROLE_ADM = 255;
    public const USER_ROLE_RW = 192;
    public const USER_ROLE_RO = 128;
    protected string $userName;
    protected string $password;
    protected string $email = '';
    protected string $fullName = '';
    protected string $firstName = '';
    protected string $lastName = '';
    protected string $token = '';
    protected ?string $tokenExpiry;
    protected int $active;
    protected int $role;
    protected static int $tokenLength = 32;
    private string $dateFormat = "Y-m-d H:i:s";
    public function setProperty(string $name, mixed $value): void
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
            return;
        }
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
    public function getProperty(string $name, mixed $default = null): mixed
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return property_exists($this, $name) ? $this->$name : $default;
    }
    public function setPassword(string $value)
    {
        $this->password = $this->hashPassword($value);
    }
    /**
     * This returns the password hash.
     * The unhashed password is never stored on the object
     * @return string the hashed value of the password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
    private function hashPassword(string $password): string
    {
        return PasswordManager::hashPassword($password);
    }
    public function verifyPassword(string $password): bool
    {
        return PasswordManager::verifyPassword($this->getPassword(), $password);
    }
    public function createToken(): string
    {
        return bin2hex(random_bytes(user::$tokenLength));
    }
    public function isTokenValid(string $token): bool
    {
        return date($this->dateFormat) <= $this->tokenExpiry && $this->token == $token;
    }
    public function resetPassword(): bool
    {
        $retval = false;
        if (!isset($this->userName) || !isset($this->email)) {
            return $retval;
        }
        $this->setProperty('token', $this->createToken());
        $this->setProperty('tokenExpiry', (new DateTime())->add(new DateInterval("PT24H"))->format($this->dateFormat));
        if ($this->update()) {
            $retval = true;
            $title = Config::instance()->get("title");
            $url = Config::instance()->get("url");
            $message = "Esta' a receber este email porque solicitou a reposicao da sua palavra-passe na aplicacao '$title'.\r\n";
            $message .= "Para continuar o processo deve clique no link abaixo para definir uma nova senha.\r\n";
            $message .= "{$url}reset_password.php?token_id={$this->getProperty('token')}.\r\n";
            $message .= "Este token e' valido ate' 'as {$this->getProperty('tokenExpiry')}.\r\n";
            $message .= "Findo este prazo tera' que reiniciar o processo usando o link {$url}forgot_password.php.\r\n";
            $message .= "\r\n";
            $message .= "Cumprimentos,\r\n";
            $message .= "$title\r\n";
            $email = new Email();
            $retval = $email->send(Config::instance()->get("from"), $this->getProperty('email'), "Reposicao de palavra-passe", $message);
        }
        return $retval;
    }
    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    abstract public static function getByUsername(string $username): ?self;

    abstract public static function getByToken(string $token): ?self;
}
