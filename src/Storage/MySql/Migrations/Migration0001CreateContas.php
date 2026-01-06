<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0001CreateContas extends AbstractMigration
{
    protected string $version = "Migration0001CreateContas";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `contas` (
  `id` int(3) NOT NULL DEFAULT 0,
  `number` char(30) NOT NULL DEFAULT '',
  `name` char(30) NOT NULL DEFAULT '',
  `grupo` int(3) NOT NULL DEFAULT 0,
  `typeId` int(2) DEFAULT NULL,
  `iban` char(24) DEFAULT NULL,
  `swift` char(24) NOT NULL DEFAULT '',
  `openDate` date DEFAULT NULL,
  `closeDate` date DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        $this->getConnection()->query("
            INSERT IGNORE INTO contas (id, number, name, typeId, iban, swift, openDate, closeDate, active)
            VALUES (1,'','Caixa (Euro)',2,'','','1990-01-01','1990-01-01',1)
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS contas;");
    }
}
