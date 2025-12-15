<?php

namespace PHPLedger\Util;

use PHPLedger\Contracts\SessionServiceInterface;

class SessionManager implements SessionServiceInterface
{
    private array $data;
    public function __construct()
    {
        $this->start();
        $this->data = $_SESSION['app'] ?? [];
        $this->isExpired();
    }
    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_only_cookies', '1');
            session_name($this->sessionName());
            session_start();
        }
    }
    public function commit(): void
    {
        $_SESSION['app'] = $this->data;
    }
    public function sessionName(): string
    {
        return "phpledger_session";
    }
    public function isAuthenticated(): bool
    {
        return isset($this->data['user']) && !$this->isExpired();
    }
    public function isExpired(): bool
    {
        return isset($this->data['expires']) && $this->data['expires'] < time();
    }
    public function logout(): void
    {
        $this->data = [];
        $_SESSION['app'] = [];
        session_unset();
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
            session_write_close();
        }
    }
    public function refreshExpiration(int $ttl = 3600): void
    {
        $this->data['expires'] = time() + $ttl;
        $this->commit();
    }
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->commit();
    }
}
