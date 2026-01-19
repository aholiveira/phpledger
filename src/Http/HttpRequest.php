<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Http;

use PHPLedger\Contracts\RequestInterface;

final class HttpRequest implements RequestInterface
{
    private string $method;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isGet(): bool
    {
        return $this->method() === "GET";
    }

    public function isPost(): bool
    {
        return $this->method() === "POST";
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return match ($this->method) {
            'POST' => $_POST[$key] ?? $default,
            default => $_GET[$key] ?? $default,
        };
    }

    public function all(): array
    {
        return match ($this->method) {
            'POST' => $_POST,
            default => $_GET,
        };
    }
}
