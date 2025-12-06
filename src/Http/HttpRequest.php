<?php

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
