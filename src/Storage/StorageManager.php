<?php

namespace PHPLedger\Storage;

use InvalidArgumentException;
use PHPLedger\Contracts\StorageEngineInterface;
use PHPLedger\Storage\MySql\MySqlEngine;

final class StorageManager
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function getEngine(string $type): StorageEngineInterface
    {
        return match ($type) {
            'mysql' => new MySqlEngine($this->app),
            default => throw new InvalidArgumentException("Unsupported storage type: $type"),
        };
    }
}
