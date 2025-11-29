<?php

if (!\defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__);
}

require __DIR__ . '/Storage/MySql/bootstrap.php';

uses(PHPLedgerTests\TestCase::class)->in();
uses(PHPLedgerTests\DatabaseTestCase::class)->in('Storage' . DIRECTORY_SEPARATOR . 'MySql');

uses()->beforeAll(function () {
    checkAndUpdateDatabaseSchema();
})->in('Storage' . DIRECTORY_SEPARATOR . 'MySql');
