<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0010FixedCostsCategory extends AbstractMigration
{
    protected string $version = "Migration0010FixedCostsCategory";
    public function up(): void
    {
        $this->getConnection()->query("ALTER TABLE tipo_mov ADD COLUMN IF NOT EXISTS fixedCost tinyint(1) NOT NULL DEFAULT 0;");
    }

    public function down(): void
    {
        $this->getConnection()->query("ALTER TABLE tipo_mov DELETE COLUMN fixedCost;");
    }
}
