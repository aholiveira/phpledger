<?php

namespace PHPLedgerTests;

use PHPLedgerTests\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected \mysqli $db;

    protected function setUp(): void {}

    protected function tearDown(): void {}
}
