<?php

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0006CreateTipoContas extends AbstractMigration
{
    protected string $version = "Migration0006CreateTipoContas";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `tipo_contas` (
  `id` int(2) NOT NULL DEFAULT 0,
  `description` char(30) DEFAULT NULL,
  `savings` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        $this->getConnection()->query("INSERT IGNORE INTO tipo_contas (id, description, savings)
        VALUES
        (1, 'Conta ficticia', 0),
        (2, 'Conta bancaria', 0);");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS tipo_contas;");
    }
}
