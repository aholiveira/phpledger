<?php

namespace PHPLedger\Exceptions;

use Exception;

class ConfigException extends Exception
{
    public const INVALID = 1;
    public const MISSING = 2;
    public const UNSUPPORTED = 4;

    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
