<?php

declare(strict_types=1);

namespace PHPLedger\Storage\MySql\Migrations;

final class Migration0003CreateGrupoContas extends AbstractMigration
{
    protected string $version = "Migration0003CreateGrupoContas";
    public function up(): void
    {
        $this->getConnection()->query("
CREATE TABLE IF NOT EXISTS `grupo_contas` (
  `id` int(4) NOT NULL DEFAULT 0,
  `nome` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        $this->getConnection()->query("INSERT IGNORE INTO `grupo_contas` (`id`, `nome`) VALUES (1, 'Default');");
    }

    public function down(): void
    {
        $this->getConnection()->query("DROP TABLE IF EXISTS grupo_contas;");
    }
}
