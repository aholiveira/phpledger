<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class mysql_storage implements iDataStorage
{
    private ?\mysqli $_dblink = null;
    private string $_message = "";
    private $_tableCreateSQL;
    private $_collation = "utf8mb4_general_ci";
    private $_engine = "InnoDB";
    private $_default_admin_username;
    private $_default_admin_password;
    private Logger $logger;

    public function __construct()
    {
        global $logger;
        $this->_message = "";
        $this->setTableCreateSQL();
        $this->_default_admin_username = config::get("admin_username");
        $this->_default_admin_password = config::get("admin_password");
        $this->logger = $logger;
    }
    private function connect(): void
    {
        $host = config::get("host");
        $user = config::get("user");
        $pass = config::get("password");
        $dbase = config::get("database");
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            if ($this->_dblink instanceof \mysqli) {
                if ($this->_dblink->connect_errno) {
                    try {
                        $this->_dblink->close();
                    } catch (\Exception $e) {
                        // Ignore errors on closing a broken connection
                    }
                    $this->_dblink = null;
                } else {
                    return;
                }
            }
            $this->_dblink = new \mysqli($host, $user, $pass, $dbase);
            $this->_dblink->set_charset('utf8mb4');
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }
    public function addMessage(string $message): string
    {
        $this->_message = ($this->_message ?? "") . "{$message}\r\n";
        return $this->message();
    }

    public function message(): string
    {
        return $this->_message ?? "";
    }
    public function check(): bool
    {
        $retval = true;
        $db_name = config::get("database");
        $tables = array_keys($this->_tableCreateSQL);
        $this->connect();
        if ($this->getDbCollation($db_name) != $this->_collation) {
            $this->addMessage("Database [{$db_name}] collation not [{$this->_collation}]");
            $retval = false;
        }
        foreach ($tables as $table_name) {
            $retval = $retval && $this->check_table($table_name);
        }
        if ($this->tableExists("tipo_mov")) {
            if (!$this->tableHasForeignKey("tipo_mov", "parent_id")) {
                $this->addMessage("Table [tipo_mov] foreign key [parent_id] missing");
                $retval = false;
            }
            $entry_category = object_factory::entry_category()::getById(0);
            if ($entry_category->id !== 0) {
                $this->addMessage("Category '0' does not exist");
                $retval = false;
            } else {
                $entry_list = $entry_category->getList(['parent_id' => ['operator' => 'is', 'value' => 'null'], 'tipo_id' => ['operator' => '>', 'value' => '0']]);
                if (sizeof($entry_list) > 0) {
                    $this->addMessage("Table [tipo_mov] needs update");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("users")) {
            try {
                $user = new user($this->_dblink);
                if (sizeof($user->getList()) == 0) {
                    $this->addMessage("Table [users] is empty");
                    $retval = false;
                }
            } catch (\Exception $ex) {
                $this->addMessage("Table [users] needs update");
                $retval = false;
            }
        }
        if ($this->tableExists("ledgers")) {
            $ledger = new ledger($this->_dblink);
            if (sizeof($ledger->getList()) == 0) {
                $this->addMessage("Table [ledgers] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("defaults")) {
            if (sizeof(defaults::getList()) == 0) {
                $this->addMessage("Table [defaults] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("moedas")) {
            $currency = new currency($this->_dblink);
            if (sizeof($currency->getList()) == 0) {
                $this->addMessage("Table [currency] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("tipo_mov")) {
            $accounttype = new accounttype($this->_dblink);
            if (sizeof($accounttype->getList()) == 0) {
                $this->addMessage("Table [account type] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("contas")) {
            $account = new account($this->_dblink);
            if (sizeof($account->getList()) == 0) {
                $this->addMessage("Table [accounts] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("movimentos")) {
            $count = $this->do_query_get_result("SELECT COUNT(*) as rowCount FROM movimentos WHERE direction=2");
            if ($count > 0) {
                $this->addMessage("Table [movimentos] needs updating [direction] column");
                $retval = false;
            }
        }
        return $retval;
    }
    public function update(): bool
    {
        $retval = true;
        $tables = array_keys($this->_tableCreateSQL);
        $this->connect();
        $db_name = config::get("database");
        if ($this->getDbCollation($db_name) != $this->_collation) {
            if ($this->setDbCollation($db_name, $this->_collation)) {
                $this->addMessage("Database [{$db_name}] collation could not be set to [{$this->_collation}]");
                $retval = false;
            }
        }
        foreach ($tables as $table_name) {
            $this->update_table($table_name);
        }
        $this->update_table_entry_type();
        $user = new user($this->_dblink);
        if (sizeof($user->getList()) == 0) {
            $user->setId(1);
            $user->setUsername($this->_default_admin_username);
            $user->setPassword($this->_default_admin_password);
            $user->setFullName('Default admin');
            $user->setEmail('');
            $user->setRole(1);
            $user->setToken('');
            $user->setTokenExpiry('');
            $user->setActive(1);
            if (!$user->update()) {
                $this->addMessage("Could not add user admin");
                $retval = false;
            }
        }
        $entry_category = object_factory::entry_category()::getById(0);
        if ($entry_category->id !== 0) {
            $entry_category->id = 0;
            $entry_category->parent_id = null;
            $entry_category->description = "Sem categoria";
            $entry_category->active = 1;
            if (!$entry_category->update()) {
                $this->addMessage("Could not add category 0");
                $retval = false;
            }
        }
        $ledger = new ledger($this->_dblink);
        if (sizeof($ledger->getList()) == 0) {
            $ledger->setId(id: 1);
            $ledger->name = "Default";
            if (!$ledger->update()) {
                $this->addMessage("Could not add default ledger");
                $retval = false;
            }
        }
        if ($this->tableExists("defaults")) {
            if (sizeof(defaults::getList()) == 0) {
                $defaults = defaults::init();
                if (!$defaults->update()) {
                    $this->addMessage("Could not save defaults");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("moedas")) {
            $currency = new currency($this->_dblink);
            if (sizeof($currency->getList()) == 0) {
                $currency->description = 'Euro';
                $currency->exchange_rate = 1;
                $currency->code = 'EUR';
                $currency->id = 1;
                if (!$currency->update()) {
                    $this->addMessage("Could not save currency");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("tipo_mov")) {
            if (sizeof(accounttype::getList()) == 0) {
                $accounttype = new accounttype($this->_dblink);
                $accounttype->description = 'Conta caixa';
                $accounttype->savings = 0;
                $accounttype->id = 1;
                if (!$accounttype->update()) {
                    $this->addMessage("Could not save account type");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("contas")) {
            $account = new account($this->_dblink);
            if (sizeof($account->getList()) == 0) {
                $account->number = '';
                $account->name = 'Caixa';
                $account->type_id = 1;
                $account->group = 1;
                $account->iban = '';
                $account->swift = '';
                $account->open_date = date("Y-m-d");
                $account->close_date = date("Y-m-d", mktime(0, 0, 0, 1, 1, 1990));
                $account->active = 1;
                $account->id = 1;
                if (!$account->update()) {
                    $this->addMessage("Could not save account");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("movimentos")) {
            $count = $this->do_query_get_result("SELECT COUNT(*) as rowCount FROM movimentos WHERE direction=2");
            if ($count > 0) {
                $result = $this->do_query("UPDATE movimentos SET direction=-1 WHERE direction=2");
                if ($result == false) {
                    $this->addMessage("Could not update [direction] column on table [movimentos]");
                    $retval = false;
                }
            }
        }
        return $retval;
    }
    public function populateRandomData(): void
    {
        global $objectFactory;
        $category = $objectFactory->entry_category();
        $ledger_entry = $objectFactory->ledgerentry();
        $start_year = date("Y") - 3;
        $end_year = date("Y");
        $max_month_entries = 100;
        $account = $objectFactory->account();
        $account_list = $account->getList(['activa' => ['operator' => '=', 'value' => '1']]);
        $category_list = $category->getList(['active' => ['operator' => '=', 'value' => '1']]);
        if (array_key_exists(0, $category_list)) {
            unset($category_list[0]);
        }

        $months = date_diff(new \DateTime(date("Y-m-d")), new \DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $start_year))));
        for ($year = $start_year; $year <= $end_year; $year++) {
            for ($month = 1; $month <= ($year == date("Y") ? date("m") : 12); $month++) {
                $days_in_month = ($year == date("Y") && $month == date("m") ? date("d") : cal_days_in_month(CAL_GREGORIAN, $month, $year));
                $entries_to_create = random_int(round(0.7 * $max_month_entries, 0), $max_month_entries);
                for ($entry_counter = 1; $entry_counter <= $entries_to_create; $entry_counter++) {
                    $ledger_entry->id = $ledger_entry->getNextId();
                    $ledger_entry->account_id = $account_list[array_rand($account_list)]->id;
                    $ledger_entry->category_id = $category_list[array_rand($category_list)]->id;
                    $ledger_entry->entry_date = date("Y-m-d", mktime(0, 0, 0, $month, random_int(1, $days_in_month), $year));
                    $ledger_entry->direction = [-1, 1][array_rand([-1, 1])];
                    $ledger_entry->currency_amount = random_int(1, 10000) / 100;
                    $ledger_entry->currency_id = 'EUR';
                    $ledger_entry->euro_amount = $ledger_entry->currency_amount * $ledger_entry->direction;
                    $ledger_entry->exchange_rate = 1;
                    $ledger_entry->username = config::get("user");
                    $ledger_entry->update();
                }
                $curr_month = date_diff(new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month, 1, $year))), new \DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $start_year))));
                print number_format(($curr_month->y * 12 + $curr_month->m) / ($months->y * 12 + $curr_month->m + 1) * 100, 1) . "%\r\n";
            }
        }
    }
    private function check_table($table_name): bool
    {
        $table_exists = $this->tableExists($table_name);
        if (!$table_exists) {
            $this->addMessage("Table [{$table_name}] does not exist");
            return false;
        }
        $retval = true;
        if ($this->getTableEngine($table_name) !== $this->_engine) {
            $this->addMessage("Table [{$table_name}] engine not [{$this->_engine}]");
            $retval = false;
        }
        if ($this->getTableCollation($table_name) !== $this->_collation) {
            $this->addMessage("Table [{$table_name}] collation not [{$this->_collation}]");
            $retval = false;
        }
        if (!empty($this->_tableCreateSQL[$table_name]['new'] ?? [])) {
            foreach ($this->_tableCreateSQL[$table_name]['new'] as $old_column_name => $new_column_name) {
                if ($this->tableHasColumn($table_name, $old_column_name) && !$this->tableHasColumn($table_name, $new_column_name)) {
                    $this->addMessage("Table [{$table_name}] column [{$old_column_name}] needs renaming to [{$new_column_name}]");
                    $retval = false;
                }
            }
        }
        $columns = $this->_tableCreateSQL[$table_name]['columns'];
        $createSQL = $this->getSQLTableCreate($table_name);
        foreach ($columns as $column => $definition) {
            if (!$this->tableHasColumn($table_name, $column)) {
                $this->addMessage("Table [{$table_name}] missing column [{$column}]");
                $retval = false;
            } elseif (stripos($createSQL, "`{$column}` {$definition}") === false) {
                $this->addMessage("Table [{$table_name}] column definition [{$column}] is wrong");
                $retval = false;
            }
        }
        return $retval;
    }
    private function update_table($table_name): bool
    {

        if (!($this->createTable($table_name))) {
            $this->addMessage("Failed to create [{$table_name}]");
            return false;
        }
        $retval = true;
        if ($this->getTableEngine($table_name) != $this->_engine) {
            if (!$this->setTableEngine($table_name, $this->_engine)) {
                $this->addMessage("Could not change engine on table [{$table_name}]");
                $retval = false;
            }
        }
        if ($this->getTableCollation($table_name) !== $this->_collation) {
            if (!$this->setTableCollation($table_name, $this->_collation)) {
                $this->addMessage("Could not change engine on table [{$table_name}]");
                $retval = false;
            }
        }
        if (!empty($this->_tableCreateSQL[$table_name]['new'] ?? [])) {
            foreach ($this->_tableCreateSQL[$table_name]['new'] as $old_column_name => $new_column_name) {
                if ($this->tableHasColumn($table_name, $old_column_name)) {
                    if (!$this->renameColumnOnTable($old_column_name, $new_column_name, $table_name)) {
                        $this->addMessage("Could not rename column [{$old_column_name}] on table [{$table_name}]");
                        $retval = false;
                    }
                }
            }
        }
        $last_column = null;
        foreach ($this->_tableCreateSQL[$table_name]['columns'] as $column_name => $column_definition) {
            if (!$this->tableHasColumn($table_name, $column_name)) {
                $definition = $column_definition . ($last_column ? " AFTER `{$last_column}`" : "");
                if (!$this->addColumnToTable($column_name, $table_name, $definition)) {
                    $this->addMessage("Could not add column [{$column_name}] to table [{$table_name}]");
                    $retval = false;
                }
            }
            $result = $this->getSQLTableCreate($table_name);
            if (stripos($result, "`{$column_name}` {$column_definition}") === false) {
                if ($this->changeColumnOnTable($column_name, $table_name, $column_definition) === false) {
                    $this->addMessage("Could not change table [{$table_name}] column [{$column_name}] definition");
                    $retval = false;
                }
            }
            $last_column = $column_name;
        }
        return $retval;
    }
    private function update_table_entry_type(): bool
    {
        $retval = true;
        if ($this->tableExists("tipo_mov")) {
            if (!$this->tableHasForeignKey("tipo_mov", "parent_id")) {
                if (!$this->addForeignKeyToTable("parent_id", "tipo_mov(tipo_id) ON DELETE CASCADE ON UPDATE CASCADE", "tipo_mov")) {
                    $this->addMessage("Could not add foreign key parent_id to table tipo_mov");
                    $retval = false;
                }
            }
            /**
             * Create new category "Uncategorized"
             * Assign all entries without category to category "Uncategorized"
             */
            global $objectFactory;
            $entry_category = $objectFactory->entry_category();
            $entry_category = $entry_category::getById(0);
            if ($entry_category->id != 0) {
                $this->addMessage("Adding category 0");
                $entry_category->id = 0;
                $entry_category->description = "Sem categoria";
                $entry_category->parent_id = null;
                $entry_category->active = 1;
                $entry_category->update();
                $sql = "UPDATE tipo_mov SET parent_id=0 WHERE parent_id is null and tipo_id not in (select parent_id from tipo_mov where parent_id is not null group by parent_id) and tipo_id > 0";
                if (!$this->do_query($sql)) {
                    $this->addMessage("Could not update categories");
                    $retval = false;
                }
            }
            if (!$this->do_query("UPDATE tipo_mov set parent_id=0 where tipo_id>0 and parent_id is null")) {
                $this->addMessage("Could not update categories with null parent");
                $retval = false;
            }
        }
        return $retval;
    }
    /**
     * @param string $sql - The query to perform. It should produce a single column and a single row.
     * @return mixed the result returned from the query
     */
    private function do_query_get_result(string $sql)
    {
        $retval = null;
        $this->connect();
        try {
            $stmt = @$this->_dblink->prepare($sql);
            if ($stmt == false) {
                return $retval;
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
        }
        return $retval;
    }

    /**
     * @param string $sql - The query to perform. It should produce a single column and a single row.
     * @return bool true if the query was successfull, false otherwise
     */
    private function do_query(string $sql)
    {
        $retval = false;
        $this->connect();
        try {
            $stmt = $this->_dblink->prepare($sql);
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $retval = $stmt->execute();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function getSQLTableCreate($table)
    {
        $retval = false;
        $this->connect();
        try {
            $stmt = $this->_dblink->prepare("SHOW CREATE TABLE `{$table}`");
            if ($stmt === false) {
                throw new \mysqli_sql_exception();
            }
            $stmt->execute();
            $stmt->bind_result($table, $retval);
            $stmt->fetch();
            $stmt->close();
        } catch (\Exception $ex) {
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function tableExists(string $table_name): bool
    {
        $retval = false;
        $this->connect();
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}'";
        try {
            $count = $this->do_query_get_result($sql);
            $retval = ($count == 1);
        } catch (\Exception $ex) {
            $this->addMessage($ex->getMessage());
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function addColumnToTable(string $column_name, string $table_name, string $typedef): bool
    {
        if ($this->tableHasColumn($table_name, $column_name)) {
            return true;
        }
        $this->connect();
        try {
            $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` $typedef";
            $retval = $this->do_query($sql);
            if ($retval) {
                $this->addMessage("Column [{$column_name}] added to [{$table_name}]");
            }
            return (bool) $retval;
        } catch (\Exception $ex) {
            $this->addMessage("Failed to add column [{$column_name}] to [{$table_name}]: " . $ex->getMessage());
            $this->logger->dump($sql ?? '', "SQL failed");
            $this->logger->dump($ex, "");
            return false;
        }
    }
    private function changeColumnOnTable(string $column_name, string $table_name, string $typedef): bool
    {
        $retval = false;
        $this->connect();
        try {
            $sql = "ALTER TABLE `{$table_name}` CHANGE COLUMN `{$column_name}` `{$column_name}` $typedef";
            $retval = $this->do_query($sql);
            $this->addMessage("Table [{$table_name}] column [{$column_name}] definition changed");
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function renameColumnOnTable(string $old_column_name, string $new_column_name, string $table_name): bool
    {
        $retval = false;
        $this->connect();
        try {
            if ($this->tableHasColumn($table_name, $new_column_name)) {
                return false;
            }
            if (!$this->tableHasColumn($table_name, $old_column_name)) {
                return false;
            }
            $sql = "ALTER TABLE `{$table_name}` RENAME COLUMN `{$old_column_name}` TO `{$new_column_name}`";
            $retval = $this->do_query($sql);
            $this->addMessage("Renamed column [{$old_column_name}] to [{$new_column_name}] on [{$table_name}]");
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->addMessage($sql);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function tableHasForeignKey(string $table_name, string $key_name): bool
    {
        $retval = false;
        $this->connect();
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}' AND CONSTRAINT_NAME='{$key_name}'";
        try {
            $count = $this->do_query_get_result($sql);
            $retval = ($count == 1);
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function addForeignKeyToTable(string $key_name, string $fk_def, string $table_name): bool
    {
        $retval = false;
        $this->connect();
        $sql = "ALTER TABLE `{$table_name}` ADD FOREIGN KEY `{$key_name}` (`{$key_name}`) REFERENCES {$fk_def}";
        try {
            if ($this->tableHasForeignKey($table_name, $key_name)) {
                return true;
            }
            $retval = $this->do_query($sql);
            $this->addMessage("Added foreign key [{$key_name}] to table [{$table_name}]");
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function tableHasColumn(string $table_name, string $column_name): bool
    {
        $this->connect();
        $sql = "SELECT count(*) as colCount
        FROM information_schema.columns
        WHERE table_name = '{$table_name}' AND column_name = '{$column_name}' and table_schema = DATABASE()";
        try {
            $count = (int) $this->do_query_get_result($sql);
            return $count === 1;
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            return false;
        }
    }
    private function getDbCollation(string $db_name): ?string
    {
        $this->connect();
        $sql = "SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='{$db_name}'";
        try {
            $retval = $this->do_query_get_result($sql);
            return $retval ?: null;
        } catch (\Exception $ex) {
            $this->addMessage("Failed to get collation for database [{$db_name}]: " . $ex->getMessage());
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            return "";
        }
    }
    private function setDbCollation(string $db_name, string $collation): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "ALTER DATABASE `{$db_name}` COLLATE='{$collation}'";
        try {
            $retval = $this->do_query($sql);
            if ($retval) {
                $this->addMessage("Changed collation on database [{$db_name}] to [{$collation}]");
            }
            return (bool) $retval;
        } catch (\Exception $ex) {
            $this->addMessage("Failed to change collation on database [{$db_name}]: " . $ex->getMessage());
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            return "";
        }
    }
    private function getTableCollation(string $table_name): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "SELECT table_collation FROM information_schema.TABLES WHERE table_name = '{$table_name}' AND table_schema = DATABASE()";
        try {
            $retval = @$this->do_query_get_result($sql);
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            $retval = "";
        }
        return $retval;
    }
    private function setTableCollation(string $table_name, string $collation): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "ALTER TABLE `{$table_name}` COLLATE='{$collation}'";
        try {
            $retval = @$this->do_query($sql);
            $this->addMessage("Changed collation on table [{$table_name}]");
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            $retval = "";
        }
        return $retval;
    }
    private function getTableEngine(string $table_name): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "SELECT ENGINE FROM information_schema.TABLES WHERE table_name = '{$table_name}' AND table_schema = DATABASE()";
        try {
            $retval = @$this->do_query_get_result($sql);
        } catch (\Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
            $retval = "";
        }
        return $retval;
    }
    private function setTableEngine(string $table_name, string $engine): bool
    {
        $retval = false;
        $this->connect();
        $sql = "ALTER TABLE `{$table_name}` ENGINE={$engine}";
        try {
            $retval = $this->do_query($sql);
            $this->addMessage("Changed engine on table [{$table_name}]");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->logger->dump($ex, "");
            $this->logger->dump($this->_dblink, "");
        }
        return $retval;
    }
    private function createTable(string $table_name)
    {
        if ($this->tableExists($table_name)) {
            return true;
        }
        if (!isset($this->_tableCreateSQL[$table_name])) {
            $this->addMessage("No create definition for table [{$table_name}]");
            return false;
        }
        $columns = [];
        $createSQL = $this->_tableCreateSQL[$table_name];
        foreach ($createSQL['columns'] as $column_name => $column_definition) {
            $columns[] = "`{$column_name}` {$column_definition}";
        }
        $columns[] = sprintf("PRIMARY KEY (`%s`)", $createSQL['primary_key']);


        if (!empty($createSQL['keys'])) {
            foreach ($createSQL['keys'] as $key_name => $key_def) {
                $columns[] = "KEY `{$key_name}` ({$key_def})";
            }
        }
        if (!empty($createSQL['constraints'])) {
            foreach ($createSQL['constraints'] as $key_name => $key_def) {
                $columns[] = " CONSTRAINT `{$key_name}` FOREIGN KEY (`{$key_name}`) REFERENCES {$key_def}";
            }
        }
        $sql = "CREATE TABLE `{$table_name}` (" . implode(",", $columns) . ")
         ENGINE={$this->_engine} DEFAULT COLLATE='{$this->_collation}'";
        try {
            $retval = $this->do_query($sql);
            if ($retval) {
                $this->addMessage("Created table [{$table_name}]");
                $this->addMessage("Created table [{$sql}]");
            }
            return (bool) $retval;
        } catch (\Exception $ex) {
            $this->addMessage("Failed to create table [{$table_name}]: " . $ex->getMessage());
            $this->logger->dump($ex);
            return false;
        }
    }
    private function setTableCreateSQL()
    {
        $tables = [
            'contas' => account::getDefinition(),
            'movimentos' => ledgerentry::getDefinition(),
            'moedas' => currency::getDefinition(),
            'defaults' => defaults::getDefinition(),
            'tipo_contas' => accounttype::getDefinition(),
            'users' => user::getDefinition(),
            'grupo_contas' => ledger::getDefinition()
        ];
        foreach ($tables as $name => $data) {
            $this->_tableCreateSQL[$name] = $data ?? [];
        }
    }
}
