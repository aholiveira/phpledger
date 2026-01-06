<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0004CreateMoedas extends AbstractMigration
{
    protected string $version = "Migration0004CreateMoedas";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `moedas` (
  `id` int(4) NOT NULL DEFAULT 0,
  `code` char(3) NOT NULL DEFAULT '',
  `description` char(30) DEFAULT NULL,
  `exchangeRate` float(8,6) DEFAULT NULL,
  `username` char(255) DEFAULT '',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        $this->getConnection()->query("INSERT IGNORE INTO `moedas` (`id`, `code`, `description`, `exchangeRate`, `username`, `createdAt`, `updatedAt`)
        VALUES (1, 'EUR', 'Euro', 1.000000, '', NULL, NULL);
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS moedas;");
    }
}
