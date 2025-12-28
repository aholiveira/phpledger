<?php

namespace PHPLedger\Contracts;

interface StorageEngineInterface
{
    public function test(array $settings): array;
    public function create(array $settings): void;
    public function runMigrations(array $settings): void;
    public function pendingMigrations(array $settings): array;
}
