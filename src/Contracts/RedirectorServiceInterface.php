<?php

namespace PHPLedger\Contracts;

interface RedirectorServiceInterface
{
    public function to(string $url, int $delay = 0): void;
}
