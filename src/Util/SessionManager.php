<?php
namespace PHPLedger\Util;
class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_only_cookies', '1');
            session_name(self::sessionName());
            session_start();
        }
    }
    public static function sessionName(): string
    {
        return "phpledger_session";
    }
    public static function isExpired(): bool
    {
        self::start();
        if (isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
            return true;
        }
        return false;
    }
    public static function logout(): void
    {
        self::start();
        session_unset();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
    }
}
