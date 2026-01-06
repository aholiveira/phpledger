<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0007CreateTipoMov extends AbstractMigration
{
    protected string $version = "Migration0007CreateTipoMov";
    public function up(): void
    {
        // tipo_mov
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `tipo_mov` (
  `id` int(3) NOT NULL DEFAULT 0,
  `parentId` int(3) DEFAULT NULL,
  `description` char(50) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`),
  CONSTRAINT `parentId` FOREIGN KEY (`parentId`) REFERENCES `tipo_mov` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        $this->getConnection()->query("INSERT IGNORE INTO tipo_mov (id, parentId, `description`, active)
            VALUES
            (0, NULL, 'Sem categoria', 1),
            (1, 0, 'Saldo inicial', 1)
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS tipo_mov;");
    }
}
