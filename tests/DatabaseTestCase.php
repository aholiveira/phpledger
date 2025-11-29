<?php

namespace PHPLedgerTests;

use PHPLedgerTests\TestCase;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Logger;

abstract class DatabaseTestCase extends TestCase
{
    protected \mysqli $db;

    protected function setUp(): void {}

    protected function tearDown(): void {}
}
