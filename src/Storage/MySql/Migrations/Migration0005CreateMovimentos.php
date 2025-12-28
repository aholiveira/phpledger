<?php

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0005CreateMovimentos extends AbstractMigration
{
    protected string $version = "Migration0005CreateMovimentos";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `movimentos` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `entryDate` date DEFAULT NULL,
  `categoryId` int(3) DEFAULT NULL,
  `accountId` int(3) DEFAULT NULL,
  `currencyId` char(3) NOT NULL DEFAULT 'EUR',
  `direction` tinyint(1) NOT NULL DEFAULT 1,
  `currencyAmount` float(10,2) DEFAULT NULL,
  `euroAmount` float(10,2) DEFAULT NULL,
  `exchangeRate` float(9,4) NOT NULL DEFAULT 1.0000,
  `a_pagar` tinyint(1) NOT NULL DEFAULT 0,
  `com_talao` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` char(255) DEFAULT NULL,
  `username` char(255) DEFAULT '',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS movimentos;");
    }
}
