<?php

namespace PHPLedger\Storage\MySql;

use mysqli;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Storage\MySql\Migrations\AbstractMigration;
use PHPLedger\Util\Path;
use Throwable;

final class MySqlMigrationRunner
{
    private mysqli $db;
    private string $namespace;
    private string $path;

    public function __construct(mysqli $db, string $namespace, string $path)
    {
        $this->db = $db;
        $this->namespace = rtrim($namespace, '\\');
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->ensureMigrationsTable();
    }

    public function run(): void
    {
        foreach ($this->getMigrations() as $className) {
            $version = basename(str_replace($this->namespace . '\\', '', $className));
            if ($this->isApplied($version)) {
                continue;
            }

            if (!class_exists($className)) {
                throw new PHPLedgerException("Migration class {$className} not found");
            }

            /** @var AbstractMigration $migration */
            $migration = new $className();

            $this->db->begin_transaction();
            try {
                $migration->setDb($this->db);
                $migration->runUp();
                $this->record($version);
                $this->db->commit();
            } catch (Throwable $e) {
                $this->db->rollback();
                throw $e;
            }
        }
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS schema_migrations (
                version VARCHAR(191) NOT NULL PRIMARY KEY,
                applied_at DATETIME NOT NULL
            ) ENGINE=InnoDB
        ");
    }

    public function isApplied(string $version): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM schema_migrations WHERE version=?"
        );
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $stmt->store_result();
        $applied = $stmt->num_rows > 0;
        $stmt->close();
        return $applied;
    }

    private function record(string $version): void
    {
        $stmt = $this->db->prepare("INSERT INTO schema_migrations (version, applied_at) VALUES (?, NOW())");
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $stmt->close();
    }

    private function getMigrations(): array
    {
        $files = glob(Path::combine($this->path, 'Migration*.php'));
        sort($files, SORT_STRING);

        $classes = [];
        foreach ($files as $file) {
            $base = basename($file, '.php');
            $classes[] = $this->namespace . '\\' . $base;
        }
        return $classes;
    }
    /**
     * Returns an associative array of all migration class names keyed by version
     *
     * @return string[] version => className
     */
    public function getAllMigrations(): array
    {
        $classes = [];
        foreach ($this->getMigrations() as $className) {
            $version = basename(str_replace($this->namespace . '\\', '', $className));
            $classes[$version] = $className;
        }
        return $classes;
    }

    /**
     * Returns an associative array of migrations that have not yet been applied
     *
     * @return string[] version => className
     */
    public function getPendingMigrations(): array
    {
        $pending = [];
        foreach ($this->getAllMigrations() as $version => $className) {
            if (!$this->isApplied($version)) {
                $pending[$version] = $className;
            }
        }
        return $pending;
    }

    /**
     * Expose DB connection for helpers like "hasAnyMigrationApplied"
     */
    public function getDb(): mysqli
    {
        return $this->db;
    }
}
