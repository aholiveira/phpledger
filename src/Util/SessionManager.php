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
        return isset($_SESSION['expires']) && $_SESSION['expires'] < time();
    }
    public static function logout(): void
    {
        self::start();
        session_unset();

        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        // Destroy the session data and close it to ensure no residual values remain
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
            session_write_close();
        }
    }
    public static function refreshExpiration(int $ttl = 3600): void
    {
        $_SESSION['expires'] = time() + $ttl;
    }
    public static function guard(array $publicPages, int $ttl): bool
    {
        self::start();
        $page = strtolower(basename($_SERVER['PHP_SELF'] ?? ''));
        $isPublic = in_array($page, $publicPages, true);

        if (self::isExpired()) {
            self::logout();
            self::start();
            if (!$isPublic && !headers_sent()) {
                Redirector::to("index.php?expired=1&lang=" . L10n::$lang);
            }
            return false;
        }

        if (!$isPublic && empty($_SESSION['user'])) {
            if (!headers_sent()) {
                Redirector::to("index.php");
            }
            return false;
        }
        self::refreshExpiration($ttl);
        return true;
    }
}
