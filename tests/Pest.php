<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

if (!\defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__);
}

require __DIR__ . '/Storage/MySql/bootstrap.php';

uses(PHPLedgerTests\TestCase::class)->in();
uses(PHPLedgerTests\DatabaseTestCase::class)->in('Storage' . DIRECTORY_SEPARATOR . 'MySql');

uses()->beforeAll(function () {
    checkAndUpdateDatabaseSchema();
})->in('Storage' . DIRECTORY_SEPARATOR . 'MySql');
