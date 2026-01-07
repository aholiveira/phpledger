<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0009AlterExchangeRate extends AbstractMigration
{
    protected string $version = "Migration0009AlterExchangeRate";
    public function up(): void
    {
        $this->getConnection()->query("ALTER TABLE movimentos MODIFY COLUMN exchangeRate float(12,8) NOT NULL DEFAULT 1.00000000;");
    }

    public function down(): void
    {
        $this->getConnection()->query("ALTER TABLE movimentos MODIFY COLUMN exchangeRate float(9,4) NOT NULL DEFAULT 1.0000;");
    }
}
