<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0008CreateUsers extends AbstractMigration
{
    protected string $version = "Migration0008CreateUsers";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(3) NOT NULL DEFAULT 0,
  `username` char(100) NOT NULL,
  `password` char(255) NOT NULL,
  `firstName` char(255) NOT NULL DEFAULT '',
  `lastName` char(255) NOT NULL DEFAULT '',
  `fullname` char(255) NOT NULL DEFAULT '',
  `email` char(255) NOT NULL DEFAULT '',
  `role` int(3) NOT NULL DEFAULT 0,
  `token` char(255) NOT NULL DEFAULT '',
  `tokenExpiry` datetime DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS users;");
    }
}
