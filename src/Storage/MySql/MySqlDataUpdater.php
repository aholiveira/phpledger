<?php

namespace PHPLedger\Storage\MySql;

use PHPLedger\Domain\EntryCategory;
use PHPLedger\Services\Config;

class MySqlDataUpdater
{
    private MySqlConnectionManager $connectionManager;
    private MySqlSchemaManager $schemaManager;
    private MySqlQueryExecutor $executor;
    private string $message = "";

    public function __construct(
        MySqlConnectionManager $connectionManager,
        MySqlSchemaManager $schemaManager,
        MySqlQueryExecutor $executor
    ) {
        $this->connectionManager = $connectionManager;
        $this->schemaManager = $schemaManager;
        $this->executor = $executor;
    }

    public function addMessage(string $msg): void
    {
        $this->message .= "{$msg}\r\n";
    }

    public function message(): string
    {
        return $this->message;
    }

    public function check(bool $test = false): bool
    {
        $retval = true;
        $dbName = Config::instance()->get("storage.settings.database");
        $tables = array_keys($this->getTableDefinitions());

        if ($this->getDbCollation($dbName) !== "utf8mb4_general_ci") {
            $this->addMessage("Database [{$dbName}] collation mismatch");
            $retval = false;
        }

        foreach ($tables as $table) {
            $retval = $retval && $this->checkTable($table);
        }

        // Additional domain-specific checks
        $retval = $retval && $this->checkUsers($test);
        $retval = $retval && $this->checkLedgerEntries($test);
        $retval = $retval && $this->checkDefaults();
        $retval = $retval && $this->checkCurrencies($test);
        $retval = $retval && $this->checkAccountTypes($test);
        $retval = $retval && $this->checkAccounts($test);
        $retval = $retval && $this->checkMovimentos();

        return $retval;
    }

    public function update(bool $test = false): bool
    {
        $retval = true;
        $dbName = Config::instance()->get("storage.settings.database");
        $tables = array_keys($this->getTableDefinitions());

        if ($this->getDbCollation($dbName) !== "utf8mb4_general_ci") {
            $this->setDbCollation($dbName, "utf8mb4_general_ci");
        }

        foreach ($tables as $table) {
            $this->updateTable($table);
        }

        $this->updateTableEntryType();
        $this->ensureDefaultUser();
        $this->ensureDefaultLedger();
        $this->ensureDefaultCurrency();
        $this->ensureDefaultAccountType();
        $this->ensureDefaultAccount();

        return $retval;
    }

    public function populateRandomData(): void
    {
        $category = MySqlObjectFactory::entryCategory();
        $ledgerEntry = MySqlObjectFactory::ledgerentry();
        $account = MySqlObjectFactory::account();

        $startYear = date("Y") - 3;
        $endYear = date("Y");
        $maxMonthEntries = 100;

        $accounts = $account->getList(['activa' => ['operator' => '=', 'value' => '1']]);
        $categories = $category->getList(['active' => ['operator' => '=', 'value' => '1']]);
        unset($categories[0]);

        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= ($year === date("Y") ? date("m") : 12); $month++) {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $entriesToCreate = random_int(round(0.7 * $maxMonthEntries), $maxMonthEntries);
                for ($i = 1; $i <= $entriesToCreate; $i++) {
                    $ledgerEntry->id = $ledgerEntry->getNextId();
                    $ledgerEntry->accountId = $accounts[array_rand($accounts)]->id;
                    $ledgerEntry->categoryId = $categories[array_rand($categories)]->id;
                    $ledgerEntry->entryDate = date("Y-m-d", mktime(0, 0, 0, $month, random_int(1, $daysInMonth), $year));
                    $ledgerEntry->direction = [-1, 1][array_rand([-1, 1])];
                    $ledgerEntry->currencyAmount = random_int(1, 10000) / 100;
                    $ledgerEntry->currencyId = 'EUR';
                    $ledgerEntry->euroAmount = $ledgerEntry->currencyAmount * $ledgerEntry->direction;
                    $ledgerEntry->exchangeRate = 1;
                    $ledgerEntry->username = Config::instance()->get("storage.settings.user", "root");
                    $ledgerEntry->update();
                }
            }
        }
    }

    // ----------------- Internal Methods -----------------

    private function checkTable(string $tableName): bool
    {
        if (!$this->schemaManager->tableExists($tableName)) {
            $this->addMessage("Table [{$tableName}] does not exist");
            return false;
        }
        return true;
    }

    private function getTableDefinitions(): array
    {
        return [
            'contas' => MySqlAccount::getDefinition(),
            'movimentos' => MySqlLedgerEntry::getDefinition(),
            'moedas' => MySqlCurrency::getDefinition(),
            'defaults' => MySqlDefaults::getDefinition(),
            'tipo_contas' => MysqlAccountType::getDefinition(),
            'users' => MySqlUser::getDefinition(),
            'grupo_contas' => MySqlLedger::getDefinition(),
            'tipo_mov' => MySqlEntryCategory::getDefinition()
        ];
    }

    private function getDbCollation(string $dbName): ?string
    {
        $sql = "SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='{$dbName}'";
        return $this->executor->fetchSingleValue($sql);
    }

    private function setDbCollation(string $dbName, string $collation): bool
    {
        $sql = "ALTER DATABASE `{$dbName}` COLLATE='{$collation}'";
        return $this->executor->executeQuery($sql);
    }

    // Additional private helper methods like:
    // checkUsers(), checkLedgerEntries(), checkDefaults(), checkCurrencies()
    // ensureDefaultUser(), ensureDefaultLedger(), etc.
    // would be implemented here for SRP, but each separated for clarity.

    private function checkUsers(bool $test): bool
    {
        if (!$this->schemaManager->tableExists("users")) {
            return true;
        }
        $user = new MySqlUser();
        if (!$test && count($user->getList()) === 0) {
            $this->addMessage("Table [users] is empty");
            return false;
        }
        return true;
    }

    private function checkLedgerEntries(bool $test): bool
    {
        if (!$this->schemaManager->tableExists("tipo_mov")) {
            return true;
        }
        $entryCategory = MySqlObjectFactory::entryCategory()::getById(0);
        if ($entryCategory->id !== 0) {
            $entries = $entryCategory->getList([
                'parentId' => ['operator' => 'is', 'value' => null],
                'id' => ['operator' => '>', 'value' => '0']
            ]);
            if (count($entries) > 0) {
                $this->addMessage("Table [tipo_mov] needs update - invalid account parents");
                return false;
            }
        }
        return true;
    }

    private function checkDefaults(): bool
    {
        if ($this->schemaManager->tableExists("defaults") && count(MySqlDefaults::getList()) === 0) {
            $this->addMessage("Table [defaults] is empty");
            return false;
        }
        return true;
    }

    private function checkCurrencies(bool $test): bool
    {
        if (!$this->schemaManager->tableExists("moedas")) {
            return true;
        }
        $currency = new MySqlCurrency();
        if (!$test && count($currency->getList()) === 0) {
            $this->addMessage("Table [currency] is empty");
            return false;
        }
        return true;
    }

    private function checkAccountTypes(bool $test): bool
    {
        if (!$this->schemaManager->tableExists("tipo_mov")) {
            return true;
        }
        $accountType = new MysqlAccountType();
        if (!$test && count($accountType->getList()) === 0) {
            $this->addMessage("Table [account type] is empty");
            return false;
        }
        return true;
    }

    private function checkAccounts(bool $test): bool
    {
        if (!$this->schemaManager->tableExists("contas")) {
            return true;
        }
        $account = new MySqlAccount();
        if (!$test && count($account->getList()) === 0) {
            $this->addMessage("Table [accounts] is empty");
            return false;
        }
        return true;
    }

    private function checkMovimentos(): bool
    {
        if (!$this->schemaManager->tableExists("movimentos")) {
            return true;
        }
        $count = $this->executor->fetchSingleValue("SELECT COUNT(*) as rowCount FROM movimentos WHERE direction=2");
        if ($count > 0) {
            $this->addMessage("Table [movimentos] needs updating [direction] column");
            return false;
        }
        return true;
    }

    private function ensureDefaultUser(): void
    {
        $user = new MySqlUser();
        if (count($user->getList()) === 0) {
            $user->setId(1);
            $user->setProperty('username', 'admin');
            $user->setProperty('password', 'admin');
            $user->setProperty('fullName', 'Default admin');
            $user->setProperty('role', 1);
            $user->update();
        }
    }

    private function ensureDefaultLedger(): void
    {
        $ledger = new MySqlLedger();
        if (count($ledger->getList()) === 0) {
            $ledger->setId(1);
            $ledger->name = "Default";
            $ledger->update();
        }
    }

    private function ensureDefaultCurrency(): void
    {
        if (!$this->schemaManager->tableExists("moedas")) return;
        $currency = new MySqlCurrency();
        if (count($currency->getList()) === 0) {
            $currency->id = 1;
            $currency->description = 'Euro';
            $currency->exchangeRate = 1;
            $currency->code = 'EUR';
            $currency->update();
        }
    }

    private function ensureDefaultAccountType(): void
    {
        if (!$this->schemaManager->tableExists("tipo_mov")) return;
        $accountType = new MysqlAccountType();
        if (count($accountType->getList()) === 0) {
            $accountType->id = 1;
            $accountType->description = 'Conta caixa';
            $accountType->savings = 0;
            $accountType->update();
        }
    }

    private function ensureDefaultAccount(): void
    {
        if (!$this->schemaManager->tableExists("contas")) return;
        $account = new MySqlAccount();
        if (count($account->getList()) === 0) {
            $account->id = 1;
            $account->name = 'Caixa';
            $account->typeId = 1;
            $account->grupo = 1;
            $account->openDate = date("Y-m-d");
            $account->closeDate = date("Y-m-d", mktime(0, 0, 0, 1, 1, 1990));
            $account->activa = 1;
            $account->update();
        }
    }

    private function updateTable(string $tableName): bool
    {
        $success = true;

        if (!$this->schemaManager->tableExists($tableName)) {
            if (!$this->schemaManager->createTable($tableName)) {
                $this->addMessage("Failed to create [{$tableName}]");
                return false;
            }
        }

        // Ensure engine
        if ($this->schemaManager->getTableEngine($tableName) !== $this->schemaManager->getDefaultEngine()) {
            if (!$this->schemaManager->setTableEngine($tableName, $this->schemaManager->getDefaultEngine())) {
                $this->addMessage("Could not change engine on table [{$tableName}]");
                $success = false;
            }
        }

        // Ensure collation
        if ($this->schemaManager->getTableCollation($tableName) !== $this->schemaManager->getDefaultCollation()) {
            if (!$this->schemaManager->setTableCollation($tableName, $this->schemaManager->getDefaultCollation())) {
                $this->addMessage("Could not change collation on table [{$tableName}]");
                $success = false;
            }
        }

        // Ensure columns
        $definition = $this->getTableDefinitions()[$tableName] ?? [];
        foreach ($definition['columns'] as $column => $typedef) {
            if (!$this->schemaManager->tableHasColumn($tableName, $column)) {
                if (!$this->schemaManager->addColumnToTable($column, $tableName, $typedef)) {
                    $this->addMessage("Could not add column [{$column}] to table [{$tableName}]");
                    $success = false;
                }
            } else {
                $this->schemaManager->changeColumnOnTable($column, $tableName, $typedef);
            }
        }

        // Handle renamed columns
        if (!empty($definition['new'] ?? [])) {
            foreach ($definition['new'] as $old => $new) {
                $this->schemaManager->renameColumnOnTable($old, $new, $tableName);
            }
        }

        return $success;
    }

    private function updateTableEntryType(): bool
    {
        $success = true;

        if (!$this->schemaManager->tableExists("tipo_mov")) {
            return true;
        }

        if (!$this->schemaManager->tableHasForeignKey("tipo_mov", "parentId")) {
            if (!$this->schemaManager->addForeignKeyToTable(
                "parentId",
                "tipo_mov(id) ON DELETE CASCADE ON UPDATE CASCADE",
                "tipo_mov"
            )) {
                $this->addMessage("Could not add foreign key parentId to table tipo_mov");
                $success = false;
            }
        }

        // Ensure default category 0 exists
        $entryCategory = MySqlObjectFactory::entryCategory()::getById(0);
        if ($entryCategory->id !== 0) {
            $entryCategory->id = 0;
            $entryCategory->description = "Sem categoria";
            $entryCategory->parentId = null;
            $entryCategory->active = 1;
            if (!$entryCategory->update()) {
                $this->addMessage("Could not add category 0");
                $success = false;
            }
        }

        // Assign all entries without parent to category 0
        $sql = "UPDATE tipo_mov SET parentId=0 WHERE id>0 AND parentId IS NULL";
        if (!$this->executor->executeQuery($sql)) {
            $this->addMessage("Could not update categories with null parent");
            $success = false;
        }

        return $success;
    }
}
