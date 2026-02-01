<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

/**
 * Global Pest setup for Storage/MySql tests.
 * Ensures database schema is up-to-date before running any MySQL storage tests.
 */

// Define ROOT_DIR FIRST before importing anything that uses it
if (!\defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
}

use PHPLedger\ApplicationFactory;
use PHPLedger\Services\Config;
use PHPLedger\Storage\StorageManager;
use PHPLedger\Util\Path;

function  checkAndUpdateDatabaseSchema()
{
    // Initialize config and logger
    Config::init(Path::combine(ROOT_DIR, 'config', 'config.json'));

    // Display check message to user
    fwrite(STDOUT, PHP_EOL . "Checking storage migrations..." . PHP_EOL);
    $app = ApplicationFactory::create();
    $config = $app->config()->getCurrent();
    $engine = (new StorageManager($app))->getEngine($config['storage']['type']);
    $pending = $engine->pendingMigrations($config['storage']['settings']);

    if (!empty($pending)) {
        fwrite(
            STDOUT,
            "[WARN] Pending migrations detected (" . implode(', ', $pending) . "). Running migrations..." . PHP_EOL
        );

        $engine->runMigrations($config['storage']['settings']);

        fwrite(STDOUT, "[OK] Storage migrated to latest version." . PHP_EOL);
    } else {
        fwrite(STDOUT, "[OK] Storage already up-to-date." . PHP_EOL);
    }
}
