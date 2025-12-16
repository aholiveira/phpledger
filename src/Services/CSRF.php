<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\CsrfServiceInterface;

class CSRF implements CsrfServiceInterface
{
    private const string TOKEN_KEY = '_csrf_token';
    private const int TOKEN_EXPIRY_SECONDS = 3600;

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = [
            'value' => $token,
            'time' => time() + self::TOKEN_EXPIRY_SECONDS,
        ];
        return $token;
    }
    public function getToken(): ?string
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return null;
        }
        if ($_SESSION[self::TOKEN_KEY]['time'] < time()) {
            $this->removeToken();
            return null;
        }
        return $_SESSION[self::TOKEN_KEY]['value'];
    }
    public function validateToken(?string $token): bool
    {
        $stored = $this->getToken();
        if (!$stored || !$token) {
            return false;
        }
        $valid = hash_equals($stored, $token);
        if ($valid) {
            $this->removeToken();
        }
        return $valid;
    }
    public function removeToken(): void
    {
        unset($_SESSION[self::TOKEN_KEY]);
    }
    public function inputField(): string
    {
        $token = $this->getToken() ?? $this->generateToken();
        return '<input type="hidden" name="' . self::TOKEN_KEY . '" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }
}
