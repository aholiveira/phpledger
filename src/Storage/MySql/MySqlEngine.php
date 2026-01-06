<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\MySql;

use mysqli;
use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\StorageEngineInterface;
use PHPLedger\Exceptions\ConfigException;
use PHPLedger\Util\Path;
use Throwable;

final class MySqlEngine implements StorageEngineInterface
{
    private ApplicationObjectInterface $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function test(array $settings): array
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $db = @new mysqli($settings['host'] ?? '', $settings['user'] ?? '', $settings['password'] ?? '', '', $settings['port'] ?? 3306);
        } catch (Throwable $e) {
            throw new ConfigException($this->app->l10n()->l('mysql_server_fail', $e->getMessage()));
        }

        try {
            $exists = $db->select_db($settings['database']);
            $message = $this->app->l10n()->l('test_db_success');
        } catch (Throwable $e) {
            $exists = false;
            $message = $this->app->l10n()->l('db_not_exist_will_create');
        }
        $db->close();
        return ['success' => true, 'message' => $message, 'db_exists' => $exists];
    }

    public function create(array $settings): void
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli($settings['host'], $settings['user'], $settings['password'], '', $settings['port']);
        if ($db->connect_error) {
            throw new ConfigException($this->app->l10n()->l('mysql_server_fail', ['error' => $db->connect_error]));
        }

        $sql = "CREATE DATABASE `" . $db->real_escape_string($settings['database']) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$db->query($sql)) {
            $db->close();
            throw new ConfigException($this->app->l10n()->l('db_create_fail', ['error' => $db->error]));
        }
        $db->close();
    }

    public function runMigrations(array $settings): void
    {
        $db = $this->getDb($settings);
        $runner = $this->getRunner($db);
        $runner->run();
        $db->close();
    }

    public function pendingMigrations(array $settings): array
    {
        $db = $this->getDb($settings);
        $runner = $this->getRunner($db);
        $pending = [];
        foreach ($runner->getAllMigrations() as $v => $f) {
            if (!$runner->isApplied($v)) {
                $pending[] = $v;
            }
        }
        $db->close();
        return $pending;
    }

    private function getDb(array $settings): mysqli
    {
        return new mysqli($settings['host'], $settings['user'], $settings['password'], $settings['database'], $settings['port']);
    }

    private function getRunner(mysqli $db): MySqlMigrationRunner
    {
        return new MySqlMigrationRunner(
            $db,
            'PHPLedger\\Storage\\MySql\\Migrations',
            Path::combine(ROOT_DIR, 'src', 'Storage', 'MySql', 'Migrations')
        );
    }
}
