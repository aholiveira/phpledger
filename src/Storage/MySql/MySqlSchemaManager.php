<?php

namespace PHPLedger\Storage\MySql;

use PHPLedger\Services\Logger;

class MySqlSchemaManager
{
    private MySqlQueryExecutor $executor;
    private string $dbCollation = "utf8mb4_general_ci";
    private string $dbEngine = "InnoDB";

    public function __construct(MySqlQueryExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function tableExists(string $tableName): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tableName}'";
        return (int)$this->executor->fetchSingleValue($sql) === 1;
    }

    public function tableHasColumn(string $tableName, string $columnName): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tableName}' AND COLUMN_NAME='{$columnName}'";
        return (int)$this->executor->fetchSingleValue($sql) === 1;
    }

    public function tableHasForeignKey(string $tableName, string $keyName): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tableName}' AND CONSTRAINT_NAME='{$keyName}'";
        return (int)$this->executor->fetchSingleValue($sql) === 1;
    }

    public function addColumn(string $tableName, string $columnName, string $definition): bool
    {
        if ($this->tableHasColumn($tableName, $columnName)) {
            return true;
        }
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$definition}";
        $success = $this->executor->executeQuery($sql);
        if ($success) {
            Logger::instance()->debug("Added column [{$columnName}] to table [{$tableName}]");
        }
        return $success;
    }

    public function changeColumn(string $tableName, string $columnName, string $definition): bool
    {
        $sql = "ALTER TABLE `{$tableName}` CHANGE COLUMN `{$columnName}` `{$columnName}` {$definition}";
        $success = $this->executor->executeQuery($sql);
        if ($success) {
            Logger::instance()->debug("Changed column [{$columnName}] definition on table [{$tableName}]");
        }
        return $success;
    }

    public function renameColumn(string $tableName, string $oldName, string $newName): bool
    {
        if (!$this->tableHasColumn($tableName, $oldName) || $this->tableHasColumn($tableName, $newName)) {
            return false;
        }
        $sql = "ALTER TABLE `{$tableName}` RENAME COLUMN `{$oldName}` TO `{$newName}`";
        $success = $this->executor->executeQuery($sql);
        if ($success) {
            Logger::instance()->debug("Renamed column [{$oldName}] to [{$newName}] on table [{$tableName}]");
        }
        return $success;
    }

    public function getTableCollation(string $tableName): ?string
    {
        $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tableName}'";
        return $this->executor->fetchSingleValue($sql) ?: null;
    }

    public function setTableCollation(string $tableName, string $collation = null): bool
    {
        $collation ??= $this->dbCollation;
        $sql = "ALTER TABLE `{$tableName}` COLLATE='{$collation}'";
        $success = $this->executor->executeQuery($sql);
        if ($success) {
            Logger::instance()->debug("Set collation [{$collation}] on table [{$tableName}]");
        }
        return $success;
    }

    public function getTableEngine(string $tableName): ?string
    {
        $sql = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$tableName}'";
        return $this->executor->fetchSingleValue($sql) ?: null;
    }

    public function setTableEngine(string $tableName, string $engine = null): bool
    {
        $engine ??= $this->dbEngine;
        $sql = "ALTER TABLE `{$tableName}` ENGINE={$engine}";
        $success = $this->executor->executeQuery($sql);
        if ($success) {
            Logger::instance()->debug("Set engine [{$engine}] on table [{$tableName}]");
        }
        return $success;
    }
}
