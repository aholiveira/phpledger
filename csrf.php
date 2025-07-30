<?php
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_EXPIRY = 3600; // seconds (1 hour)

    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = [
            'value' => $token,
            'time' => time(),
        ];
        return $token;
    }

    public static function getToken(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return null;
        }
        if (time() - $_SESSION[self::TOKEN_KEY]['time'] > self::TOKEN_EXPIRY) {
            self::removeToken();
            return null;
        }
        return $_SESSION[self::TOKEN_KEY]['value'];
    }

    public static function validateToken(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $storedToken = self::getToken();
        if (!$storedToken || !$token) {
            return false;
        }
        $valid = hash_equals($storedToken, $token);
        if ($valid) {
            self::removeToken(); // single-use token
        }
        return $valid;
    }

    public static function removeToken(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        unset($_SESSION[self::TOKEN_KEY]);
    }

    // Helper to output hidden input field with token for forms
    public static function inputField(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}
