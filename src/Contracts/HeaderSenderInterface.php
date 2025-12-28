<?php

namespace PHPLedger\Contracts;

interface HeaderSenderInterface
{
    public function send(string $header, bool $replace = true, int $code = 0): void;
    public function sent(): bool;
}
