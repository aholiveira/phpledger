<?php
namespace PHPLedger\Util;
class CSRF
{
    private const string TOKEN_KEY = '_csrf_token';
    private const int TOKEN_EXPIRY_SECONDS = 3600;

    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = [
            'value' => $token,
            'time' => time() + (int) self::TOKEN_EXPIRY_SECONDS,
        ];
        return $token;
    }
    public static function getToken(): ?string
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return null;
        }
        if ($_SESSION[self::TOKEN_KEY]['time'] < time()) {
            self::removeToken();
            return null;
        }
        return $_SESSION[self::TOKEN_KEY]['value'];
    }
    public static function validateToken(?string $token): bool
    {
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
        unset($_SESSION[self::TOKEN_KEY]);
    }
    public static function inputField(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_KEY . '" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}
