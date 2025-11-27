<?php

namespace PHPLedgerTests;

use PHPLedgerTests\TestCase;
use PHPLedger\Storage\MySql\MySqlStorage;

abstract class DatabaseTestCase extends TestCase
{
    protected \mysqli $db;

    protected function setUp(): void
    {
        // reset singleton so each test gets fresh connection
        $ref = new \ReflectionClass(MySqlStorage::class);
        $prop = $ref->getProperty('instance');
        $prop->setValue(null);
        $config = json_decode(file_get_contents(__DIR__ . '../test/config.json'), true);
        $this->db = new \mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['db']
        );

        if ($this->db->connect_errno) {
            throw new \RuntimeException("Test DB connection failed");
        }

        // inject db into storage singleton
        $storage = MySqlStorage::instance();
        $r = new \ReflectionClass($storage);
        $p = $r->getProperty('dbConnection');
        $p->setValue($storage, $this->db);

        // clean tables automatically
        $this->resetTables();
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    private function resetTables(): void
    {
        // truncate ALL tables related to MySQL storage
        $tables = ['account', 'account_type', 'entry', 'entry_category'];
        foreach ($tables as $tbl) {
            #$this->db->query("TRUNCATE TABLE `$tbl`");
        }
    }
}
