<?php

namespace PHPLedger\Contracts;

interface TimezoneServiceInterface
{
    public function apply(string $default = "UTC"): string;
}
