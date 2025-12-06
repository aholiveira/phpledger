<?php

namespace PHPLedger\Contracts;

interface RequestInterface
{
    public function method(): string;
    public function input(string $key, mixed $default = null);
    public function all(): array;
}
