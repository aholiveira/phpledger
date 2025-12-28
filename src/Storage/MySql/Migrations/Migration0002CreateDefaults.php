<?php

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0002CreateDefaults extends AbstractMigration
{
    protected string $version = "Migration0002CreateDefaults";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `defaults` (
  `id` int(1) NOT NULL DEFAULT 0,
  `categoryId` int(3) DEFAULT NULL,
  `accountId` int(3) DEFAULT NULL,
  `currencyId` char(3) DEFAULT NULL,
  `entryDate` date DEFAULT NULL,
  `direction` enum('1','-1') DEFAULT NULL,
  `language` char(10) DEFAULT NULL,
  `lastVisitedUri` char(255) DEFAULT NULL,
  `lastVisitedAt` int(11) DEFAULT NULL,
  `username` char(100) DEFAULT NULL,
  `showReportGraph` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS defaults;");
    }
}
