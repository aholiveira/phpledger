<?php

/**
 * Global Pest setup for Storage/MySql tests.
 * Ensures database schema is up-to-date before running any MySQL storage tests.
 */

// Define ROOT_DIR FIRST before importing anything that uses it
if (!\defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
}

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;
use PHPLedger\Util\LogLevel;

function  checkAndUpdateDatabaseSchema()
{
    // Initialize config and logger
    Config::init(ROOT_DIR . DIRECTORY_SEPARATOR . 'config.json');
    Logger::init(ROOT_DIR . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'ledger.log', LogLevel::DEBUG);
    ObjectFactory::init("mysql");
    // Ensure database schema is up-to-date before running any tests
    $storage = ObjectFactory::dataStorage();

    // Display check message to user
    fwrite(STDERR, PHP_EOL . "Checking database schema..." . PHP_EOL);

    if (!$storage->check(true)) {
        // Database needs updating
        fwrite(STDERR, "[WARN] Database schema out of date. Running update..." . PHP_EOL);
        $updated = $storage->update(true);
        if (!$updated) {
            throw new \RuntimeException("[X] Failed to update database schema: " . $storage->message());
        }
        fwrite(STDERR, "[OK] Database schema updated successfully." . PHP_EOL);
    } else {
        // Database is already up-to-date
        fwrite(STDERR, "[OK] Database schema is up-to-date." . PHP_EOL);
    }
}
