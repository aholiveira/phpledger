<?php
namespace PHPLedger\Util;
class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function isExpired(): bool
    {
        self::start();
        if (isset($_SESSION['expires']) && $_SESSION['expires'] < time() || !isset($_SESSION['user'])) {
            return true;
        }
        return false;
    }
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}
