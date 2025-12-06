<?php

namespace PHPLedger\Contracts;

interface SessionServiceInterface
{
    public function start(): void;
    public function isExpired(): bool;
    public function logout(): void;
    public function refreshExpiration(int $ttl = 3600): void;
    public function set(string $key, mixed $value): void;
    public function get(string $key, mixed $default = null): mixed;
}
