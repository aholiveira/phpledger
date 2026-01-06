<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Services;

use PHPLedger\Application;
use PHPLedger\Storage\StorageManager;

final class SetupService
{
    public function __construct(private Application $app) {}

    public function needsSetup(): bool
    {
        $config = $this->app->config()->getCurrent();

        if ($this->isStorageConfigMissing($config)) {
            return true;
        }

        try {
            return $this->isDbMissingOrPendingMigrations($config) || $this->isAdminUserMissing();
        } catch (\Throwable) {
            return true;
        }
    }

    private function isStorageConfigMissing(array $config): bool
    {
        return empty($config['storage']['type']) || empty($config['storage']['settings']);
    }

    private function isDbMissingOrPendingMigrations(array $config): bool
    {
        $engine = (new StorageManager($this->app))->getEngine($config['storage']['type']);
        $test = $engine->test($config['storage']['settings']);
        if (!$test['db_exists']) {
            return true;
        }
        return !empty($engine->pendingMigrations($config['storage']['settings']));
    }

    private function isAdminUserMissing(): bool
    {
        $users = $this->app->dataFactory()::user()->getList();
        return empty($users);
    }
}
