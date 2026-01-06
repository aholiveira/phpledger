<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

use mysqli;
use PHPLedger\Exceptions\PHPLedgerException;

abstract class AbstractMigration
{
    protected string $version = "";
    abstract public function up(): void;
    abstract public function down(): void;

    protected mysqli $db;

    public function setDb(mysqli $db): void
    {
        $this->db = $db;
    }

    protected function getConnection()
    {
        return $this->db;
    }

    public function runUp(): void
    {
        $this->up();
    }

    public function runDown(): void
    {
        $this->down();
        $this->unregister();
    }

    public function unregister(): void
    {
        if (empty($this->version ?? '')) {
            throw new PHPLedgerException("Migration file does not define version: " . __FILE__);
        }
        $stmt = $this->getConnection()->prepare("DELETE FROM schema_migrations WHERE version=?");
        $stmt->bind_param('s', $this->version);
        $stmt->execute();
        $stmt->close();
    }
}
