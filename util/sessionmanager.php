<?php
class SessionManager
{
    public static function start(): void
    {
        $secure = !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1);
        $cookie_params = [
            'lifetime' => 0,
            'path' => dirname($_SERVER['SCRIPT_NAME']) . '/',
            'samesite' => 'Strict',
            'secure' => $secure,
            'httponly' => true
        ];
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params($cookie_params);
            session_start();
        }
    }
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}
