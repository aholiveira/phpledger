<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use Exception;
use mysqli_sql_exception;
use mysqli;
use PHPLedger\Contracts\DataStorageInterface;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;
use RuntimeException;
use Throwable;

class MySqlStorage implements DataStorageInterface
{
    private ?mysqli $dbConnection = null;
    private string $message = "";
    private $tableCreateSQL;
    private $dbCollation = "utf8mb4_general_ci";
    private $dbEngine = "InnoDB";
    private $defaultAdminUsername;
    private $defaultAdminPassword;
    private static ?self $instance = null;
    public function __construct()
    {
        $this->message = "";
        $this->setTableCreateSQL();
        $this->defaultAdminUsername = Config::instance()->get("admin.username", "admin");
        $this->defaultAdminPassword = Config::instance()->get("admin.password", "admin");
    }
    public static function instance(): self
    {
        return self::$instance ??= new self();
    }
    public static function getConnection(): mysqli
    {
        self::instance()->connect();
        return self::instance()->dbConnection;
    }
    private function connect(): void
    {
        $host = Config::instance()->get("storage.settings.host", "localhost");
        $user = Config::instance()->get("storage.settings.user", "root");
        $pass = Config::instance()->get("storage.settings.password", "");
        $dbase = Config::instance()->get("storage.settings.database", "contas_test");
        $port = Config::instance()->get("storage.settings.port", 3306);
        $ssl = Config::instance()->get("storage.settings.ssl", false);
        if ($port !== null) {
            $host .= ':' . $port;
        }
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            if ($this->dbConnection instanceof mysqli) {
                if ($this->dbConnection->connect_errno) {
                    try {
                        $this->dbConnection->close();
                    } catch (Exception $e) {
                        // Ignore errors on closing a broken connection
                    }
                    $this->dbConnection = null;
                } else {
                    return;
                }
            }
            $this->dbConnection = new mysqli($host, $user, $pass, $dbase);
            Logger::instance()->debug("MySQL connection established");
            Logger::instance()->debug("MySQL host: {$host}");
            Logger::instance()->debug("MySQL database: {$dbase}");
            if ($ssl) {
                Logger::instance()->info("Establishing MySQL SSL connection");
                mysqli_ssl_set($this->dbConnection, null, null, null, null, null);
            }
            $this->dbConnection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        } finally {
            mysqli_report(MYSQLI_REPORT_OFF);
        }
    }
    public function addMessage(string $message): string
    {
        $this->message = ($this->message ?? "") . "{$message}\r\n";
        return $this->message();
    }

    public function message(): string
    {
        return $this->message ?? "";
    }

    public function check(bool $test = false): bool
    {
        $retval = true;
        $db_name =  Config::instance()->get("storage.settings.database");
        $tables = array_keys($this->tableCreateSQL);
        $this->connect();
        if ($this->getDbCollation($db_name) != $this->dbCollation) {
            $this->addMessage("Database [{$db_name}] collation not [{$this->dbCollation}]");
            $retval = false;
        }
        foreach ($tables as $table_name) {
            $retval = $retval && $this->checkTable($table_name);
        }
        if ($this->tableExists("tipo_mov")) {
            if (!$this->tableHasForeignKey("tipo_mov", "parentId")) {
                $this->addMessage("Table [tipo_mov] foreign key [parentId] missing");
                $retval = false;
            }
            if (!$test) {
                $entry_category = MySqlObjectFactory::entryCategory()::getById(0);
                if ($entry_category->id !== 0) {
                    $this->addMessage("Category '0' does not exist");
                    $retval = false;
                } else {
                    $entry_list = $entry_category->getList(['parentId' => ['operator' => 'is', 'value' => null], 'id' => ['operator' => '>', 'value' => '0']]);
                    if (\sizeof($entry_list) > 0) {
                        $this->addMessage("Table [tipo_mov] needs update - invalid account parents");
                        $retval = false;
                    }
                }
            }
        }
        if ($this->tableExists("users")) {
            try {
                $user = new MySqlUser();
                if (!$test && \sizeof($user->getList()) === 0) {
                    $this->addMessage("Table [users] is empty");
                    $retval = false;
                }
            } catch (Exception) {
                $this->addMessage("Table [users] needs update");
                $retval = false;
            }
        }
        if ($this->tableExists("ledgers")) {
            $ledger = new MySqlLedger();
            if (!$test && \sizeof($ledger->getList()) === 0) {
                $this->addMessage("Table [ledgers] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("defaults") && sizeof(MySqlDefaults::getList()) == 0) {
            $this->addMessage("Table [defaults] is empty");
            $retval = false;
        }
        if ($this->tableExists("moedas")) {
            $currency = new MySqlCurrency();
            if (!$test && \sizeof($currency->getList()) === 0) {
                $this->addMessage("Table [currency] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("tipo_mov")) {
            $accounttype = new MysqlAccountType();
            if (!$test && \sizeof($accounttype->getList()) === 0) {
                $this->addMessage("Table [account type] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("contas")) {
            $account = new MySqlAccount();
            if (!$test && sizeof($account->getList()) === 0) {
                $this->addMessage("Table [accounts] is empty");
                $retval = false;
            }
        }
        if ($this->tableExists("movimentos")) {
            $count = $this->fetchSingleValue("SELECT COUNT(*) as rowCount FROM movimentos WHERE direction=2");
            if ($count > 0) {
                $this->addMessage("Table [movimentos] needs updating [direction] column");
                $retval = false;
            }
        }
        return $retval;
    }
    public function update(bool $test = false): bool
    {
        $retval = true;
        $tables = array_keys($this->tableCreateSQL);
        $db_name =  Config::instance()->get("storage.settings.database");
        if (
            $this->getDbCollation($db_name) != $this->dbCollation &&
            $this->setDbCollation($db_name, $this->dbCollation)
        ) {
            $this->addMessage("Database [{$db_name}] collation could not be set to [{$this->dbCollation}]");
            $retval = false;
        }
        foreach ($tables as $table_name) {
            $this->updateTable($table_name);
        }
        $this->updateTableEntryType();
        $user = new MySqlUser();
        if (sizeof($user->getList()) == 0) {
            $user->setId(1);
            $user->setProperty('username', $this->defaultAdminUsername);
            $user->setProperty('password', $this->defaultAdminPassword);
            $user->setProperty('fullName', 'Default admin');
            $user->setProperty('email', '');
            $user->setProperty('role', 1);
            $user->setProperty('token', '');
            $user->setProperty('tokenExpiry', '');
            $user->setProperty('active', 1);
            if (!$user->update()) {
                $this->addMessage("Could not add user admin");
                $retval = false;
            }
        }
        $entry_category = MySqlObjectFactory::entryCategory()::getById(0);
        if ($entry_category->id !== 0) {
            $entry_category->id = 0;
            $entry_category->parentId = null;
            $entry_category->description = "Sem categoria";
            $entry_category->active = 1;
            if (!$entry_category->update()) {
                $this->addMessage("Could not add category 0");
                $retval = false;
            }
        }
        $entry_list = $entry_category->getList(['parentId' => ['operator' => 'is', 'value' => null], 'id' => ['operator' => '>', 'value' => '0']]);
        if (\sizeof($entry_list) > 0) {
            foreach ($entry_list as $entry) {
                $this->addMessage("Fixing entry category" . $entry->id);
                if ($entry instanceof EntryCategory) {
                    $entry->parentId = 0;
                    $entry->update();
                }
            }
            $this->addMessage("Table [tipo_mov] needs update - invalid account parents");
            $retval = false;
        }
        $ledger = new MySqlLedger();
        if (sizeof($ledger->getList()) == 0) {
            $ledger->setId(id: 1);
            $ledger->name = "Default";
            if (!$ledger->update()) {
                $this->addMessage("Could not add default ledger");
                $retval = false;
            }
        }
        if ($this->tableExists("defaults") && sizeof(MySqlDefaults::getList()) == 0) {
            $defaults = MySqlDefaults::init();
            if (!$defaults->update()) {
                $this->addMessage("Could not save defaults");
                $retval = false;
            }
        }
        if ($this->tableExists("moedas")) {
            $currency = new MySqlCurrency();
            if (sizeof($currency->getList()) == 0) {
                $currency->description = 'Euro';
                $currency->exchangeRate = 1;
                $currency->code = 'EUR';
                $currency->id = 1;
                if (!$currency->update()) {
                    $this->addMessage("Could not save currency");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("tipo_mov") && \sizeof(MysqlAccountType::getList()) == 0) {
            $accounttype = new MysqlAccountType();
            $accounttype->description = 'Conta caixa';
            $accounttype->savings = 0;
            $accounttype->id = 1;
            if (!$accounttype->update()) {
                $this->addMessage("Could not save account type");
                $retval = false;
            }
        }

        if ($this->tableExists("contas")) {
            $account = new MySqlAccount();
            if (sizeof($account->getList()) == 0) {
                $account->number = '';
                $account->name = 'Caixa';
                $account->typeId = 1;
                $account->grupo = 1;
                $account->iban = '';
                $account->swift = '';
                $account->openDate = date("Y-m-d");
                $account->closeDate = date("Y-m-d", mktime(0, 0, 0, 1, 1, 1990));
                $account->activa = 1;
                $account->id = 1;
                if (!$account->update()) {
                    $this->addMessage("Could not save account");
                    $retval = false;
                }
            }
        }
        if ($this->tableExists("movimentos")) {
            $count = $this->fetchSingleValue("SELECT COUNT(*) as rowCount FROM movimentos WHERE direction=2");
            if ($count > 0) {
                $result = $this->executeQuery("UPDATE movimentos SET direction=-1 WHERE direction=2");
                if (!$result) {
                    $this->addMessage("Could not update [direction] column on table [movimentos]");
                    $retval = false;
                }
            }
        }
        return $retval;
    }
    public function populateRandomData(): void
    {
        $category = MySqlObjectFactory::entryCategory();
        $ledgerEntry = MySqlObjectFactory::ledgerentry();
        $start_year = date("Y") - 3;
        $end_year = date("Y");
        $max_month_entries = 100;
        $account = MySqlObjectFactory::account();
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
                    $ledgerEntry->id = $ledgerEntry->getNextId();
                    $ledgerEntry->accountId = $account_list[array_rand($account_list)]->id;
                    $ledgerEntry->categoryId = $category_list[array_rand($category_list)]->id;
                    $ledgerEntry->entryDate = date("Y-m-d", mktime(0, 0, 0, $month, random_int(1, $days_in_month), $year));
                    $ledgerEntry->direction = [-1, 1][array_rand([-1, 1])];
                    $ledgerEntry->currencyAmount = random_int(1, 10000) / 100;
                    $ledgerEntry->currencyId = 'EUR';
                    $ledgerEntry->euroAmount = $ledgerEntry->currencyAmount * $ledgerEntry->direction;
                    $ledgerEntry->exchangeRate = 1;
                    $ledgerEntry->username =  Config::instance()->get("storage.settings.user", "root");
                    $ledgerEntry->update();
                }
                $curr_month = date_diff(new \DateTime(date("Y-m-d", mktime(0, 0, 0, $month, 1, $year))), new \DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $start_year))));
                print number_format(($curr_month->y * 12 + $curr_month->m) / ($months->y * 12 + $curr_month->m + 1) * 100, 1) . "%\r\n";
            }
        }
    }
    private function checkTable($table_name): bool
    {
        $table_exists = $this->tableExists($table_name);
        if (!$table_exists) {
            $this->addMessage("Table [{$table_name}] does not exist");
            return false;
        }
        $retval = true;
        if ($this->getTableEngine($table_name) !== $this->dbEngine) {
            $this->addMessage("Table [{$table_name}] engine not [{$this->dbEngine}]");
            $retval = false;
        }
        if ($this->getTableCollation($table_name) !== $this->dbCollation) {
            $this->addMessage("Table [{$table_name}] collation not [{$this->dbCollation}]");
            $retval = false;
        }
        if (!empty($this->tableCreateSQL[$table_name]['new'] ?? [])) {
            foreach ($this->tableCreateSQL[$table_name]['new'] as $old_column_name => $new_column_name) {
                if ($this->tableHasColumn($table_name, $old_column_name) && !$this->tableHasColumn($table_name, $new_column_name)) {
                    $this->addMessage("Table [{$table_name}] column [{$old_column_name}] needs renaming to [{$new_column_name}]");
                    $retval = false;
                }
            }
        }
        $columns = $this->tableCreateSQL[$table_name]['columns'];
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
    private function updateTable($table_name): bool
    {

        if (!($this->createTable($table_name))) {
            $this->addMessage("Failed to create [{$table_name}]");
            return false;
        }
        $retval = true;
        if ($this->getTableEngine($table_name) != $this->dbEngine) {
            if (!$this->setTableEngine($table_name, $this->dbEngine)) {
                $this->addMessage("Could not change engine on table [{$table_name}]");
                $retval = false;
            }
        }
        if ($this->getTableCollation($table_name) !== $this->dbCollation) {
            if (!$this->setTableCollation($table_name, $this->dbCollation)) {
                $this->addMessage("Could not change engine on table [{$table_name}]");
                $retval = false;
            }
        }
        if (!empty($this->tableCreateSQL[$table_name]['new'] ?? [])) {
            foreach ($this->tableCreateSQL[$table_name]['new'] as $old_column_name => $new_column_name) {
                if (
                    $this->tableHasColumn($table_name, $old_column_name) &&
                    !$this->renameColumnOnTable($old_column_name, $new_column_name, $table_name)
                ) {
                    $this->addMessage("Could not rename column [{$old_column_name}] on table [{$table_name}]");
                    $retval = false;
                }
            }
        }
        $last_column = null;
        foreach ($this->tableCreateSQL[$table_name]['columns'] as $column_name => $column_definition) {
            if (!$this->tableHasColumn($table_name, $column_name)) {
                $definition = $column_definition . ($last_column ? " AFTER `{$last_column}`" : "");
                if (!$this->addColumnToTable($column_name, $table_name, $definition)) {
                    $this->addMessage("Could not add column [{$column_name}] to table [{$table_name}]");
                    $retval = false;
                }
            }
            $result = $this->getSQLTableCreate($table_name);
            if (
                stripos($result, "`{$column_name}` {$column_definition}") === false &&
                $this->changeColumnOnTable($column_name, $table_name, $column_definition) === false
            ) {
                $this->addMessage("Could not change table [{$table_name}] column [{$column_name}] definition");
                $retval = false;
            }
            $last_column = $column_name;
        }
        return $retval;
    }
    private function updateTableEntryType(): bool
    {
        $retval = true;
        if ($this->tableExists("tipo_mov")) {
            if (!$this->tableHasForeignKey("tipo_mov", "parentId")) {
                if (!$this->addForeignKeyToTable("parentId", "tipo_mov(id) ON DELETE CASCADE ON UPDATE CASCADE", "tipo_mov")) {
                    $this->addMessage("Could not add foreign key parentId to table tipo_mov");
                    $retval = false;
                }
            }
            /**
             * Create new category "Uncategorized"
             * Assign all entries without category to category "Uncategorized"
             */
            $entry_category = MySqlObjectFactory::entryCategory();
            $entry_category = $entry_category::getById(0);
            if ($entry_category->id != 0) {
                $this->addMessage("Adding category 0");
                $entry_category->id = 0;
                $entry_category->description = "Sem categoria";
                $entry_category->parentId = null;
                $entry_category->active = 1;
                $entry_category->update();
                $sql = "UPDATE tipo_mov SET parentId=0 WHERE parentId is null and id not in (select parentId from tipo_mov where parentId is not null group by parentId) and id > 0";
                if (!$this->executeQuery($sql)) {
                    $this->addMessage("Could not update categories");
                    $retval = false;
                }
            }
            if (!$this->executeQuery("UPDATE tipo_mov set parentId=0 where id>0 and parentId is null")) {
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
    private function fetchSingleValue(string $sql)
    {
        $retval = null;
        try {
            $stmt = self::getConnection()->prepare($sql);
            if (!$stmt) {
                return $retval;
            }
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $ex) {
            $this->addMessage($ex->getMessage());
            $this->handleException($ex);
        }
        return $retval;
    }

    /**
     * @param string $sql - The query to perform. It should produce a single column and a single row.
     * @return bool true if the query was successfull, false otherwise
     */
    private function executeQuery(string $sql)
    {
        $retval = false;
        try {
            $stmt = self::getConnection()->prepare($sql);
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $retval = $stmt->execute();
            $stmt->close();
        } catch (Exception $ex) {
            $this->addMessage($ex->getMessage());
            $this->handleException($ex);
        }
        return $retval;
    }
    private function getSQLTableCreate($table)
    {
        $retval = false;
        try {
            $stmt = self::getConnection()->prepare("SHOW CREATE TABLE `{$table}`");
            if ($stmt === false) {
                throw new mysqli_sql_exception();
            }
            $stmt->execute();
            $stmt->bind_result($table, $retval);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $ex) {
            $this->handleException($ex);
        }
        return $retval;
    }
    private function tableExists(string $table_name): bool
    {
        $retval = false;
        $this->connect();
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}'";
        try {
            $count = $this->fetchSingleValue($sql);
            $retval = ($count == 1);
        } catch (Exception $ex) {
            $this->addMessage($ex->getMessage());
            $this->handleException($ex);
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
            $retval = $this->executeQuery($sql);
            if ($retval) {
                $this->addMessage("Column [{$column_name}] added to [{$table_name}]");
            }
            return (bool) $retval;
        } catch (Exception $ex) {
            $this->addMessage("Failed to add column [{$column_name}] to [{$table_name}]: " . $ex->getMessage());
            $this->handleException($ex);
            return false;
        }
    }
    private function changeColumnOnTable(string $column_name, string $table_name, string $typedef): bool
    {
        $retval = false;
        try {
            $sql = "ALTER TABLE `{$table_name}` CHANGE COLUMN `{$column_name}` `{$column_name}` $typedef";
            $retval = $this->executeQuery($sql);
            $this->addMessage("Table [{$table_name}] column [{$column_name}] definition changed");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->addMessage($sql);
            $this->handleException($ex);
        }
        return $retval;
    }
    private function renameColumnOnTable(string $old_column_name, string $new_column_name, string $table_name): bool
    {
        $retval = false;
        try {
            if ($this->tableHasColumn($table_name, $new_column_name)) {
                return false;
            }
            if (!$this->tableHasColumn($table_name, $old_column_name)) {
                return false;
            }
            $sql = "ALTER TABLE `{$table_name}` RENAME COLUMN `{$old_column_name}` TO `{$new_column_name}`";
            $retval = $this->executeQuery($sql);
            $this->addMessage("Renamed column [{$old_column_name}] to [{$new_column_name}] on [{$table_name}]");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->addMessage($sql);
            $this->handleException($ex);
        }
        return $retval;
    }
    private function tableHasForeignKey(string $table_name, string $key_name): bool
    {
        $retval = false;
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}' AND CONSTRAINT_NAME='{$key_name}'";
        try {
            $count = $this->fetchSingleValue($sql);
            $retval = ($count == 1);
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
        }
        return $retval;
    }
    private function addForeignKeyToTable(string $key_name, string $fk_def, string $table_name): bool
    {
        $retval = false;
        $sql = "ALTER TABLE `{$table_name}` ADD FOREIGN KEY `{$key_name}` (`{$key_name}`) REFERENCES {$fk_def}";
        try {
            if ($this->tableHasForeignKey($table_name, $key_name)) {
                return true;
            }
            $retval = $this->executeQuery($sql);
            $this->addMessage("Added foreign key [{$key_name}] to table [{$table_name}]");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
        }
        return $retval;
    }
    private function tableHasColumn(string $table_name, string $column_name): bool
    {
        $sql = "SELECT count(*) as colCount
        FROM information_schema.columns
        WHERE table_name = '{$table_name}' AND column_name = '{$column_name}' and table_schema = DATABASE()";
        try {
            $count = (int) $this->fetchSingleValue($sql);
            return $count === 1;
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
            return false;
        }
    }
    private function getDbCollation(string $db_name): ?string
    {
        $this->connect();
        $sql = "SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='{$db_name}'";
        try {
            $retval = $this->fetchSingleValue($sql);
            return $retval ?: null;
        } catch (Exception $ex) {
            $this->addMessage("Failed to get collation for database [{$db_name}]: " . $ex->getMessage());
            $this->handleException($ex);
            return "";
        }
    }
    private function setDbCollation(string $db_name, string $dbCollation): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "ALTER DATABASE `{$db_name}` COLLATE='{$dbCollation}'";
        try {
            $retval = $this->executeQuery($sql);
            if ($retval) {
                $this->addMessage("Changed collation on database [{$db_name}] to [{$dbCollation}]");
            }
            return (bool) $retval;
        } catch (Exception $ex) {
            $this->addMessage("Failed to change collation on database [{$db_name}]: " . $ex->getMessage());
            $this->addMessage($ex);
            $this->handleException($ex);
            return "";
        }
    }
    private function getTableCollation(string $table_name): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "SELECT table_collation FROM information_schema.TABLES WHERE table_name = '{$table_name}' AND table_schema = DATABASE()";
        try {
            $retval = @$this->fetchSingleValue($sql);
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
            $retval = "";
        }
        return $retval;
    }
    private function setTableCollation(string $table_name, string $dbCollation): ?string
    {
        $retval = "";
        $this->connect();
        $sql = "ALTER TABLE `{$table_name}` COLLATE='{$dbCollation}'";
        try {
            $retval = @$this->executeQuery($sql);
            $this->addMessage("Changed collation on table [{$table_name}]");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
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
            $retval = @$this->fetchSingleValue($sql);
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
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
            $retval = $this->executeQuery($sql);
            $this->addMessage("Changed engine on table [{$table_name}]");
        } catch (Exception $ex) {
            $this->addMessage($ex);
            $this->handleException($ex);
        }
        return $retval;
    }
    private function createTable(string $table_name)
    {
        if ($this->tableExists($table_name)) {
            return true;
        }
        if (!isset($this->tableCreateSQL[$table_name])) {
            $this->addMessage("No create definition for table [{$table_name}]");
            return false;
        }
        $columns = [];
        $createSQL = $this->tableCreateSQL[$table_name];
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
         ENGINE={$this->dbEngine} DEFAULT COLLATE='{$this->dbCollation}'";
        try {
            $retval = $this->executeQuery($sql);
            if ($retval) {
                $this->addMessage("Created table [{$table_name}]");
                $this->addMessage("Created table [{$sql}]");
            }
            return (bool) $retval;
        } catch (Exception $ex) {
            $this->addMessage("Failed to create table [{$table_name}]: " . $ex->getMessage());
            $this->handleException($ex);
            return false;
        }
    }
    private function setTableCreateSQL()
    {
        $tables = [
            'contas' => MySqlAccount::getDefinition(),
            'movimentos' => MySqlLedgerEntry::getDefinition(),
            'moedas' => MySqlCurrency::getDefinition(),
            'defaults' => MySqlDefaults::getDefinition(),
            'tipo_contas' => MysqlAccountType::getDefinition(),
            'users' => MySqlUser::getDefinition(),
            'grupo_contas' => MySqlLedger::getDefinition(),
            'tipo_mov' => MySqlEntryCategory::getDefinition()
        ];
        foreach ($tables as $name => $data) {
            $this->tableCreateSQL[$name] = $data ?? [];
        }
    }
    private function handleException(Throwable $e): void
    {
        Logger::instance()->error("Unhandled exception: " . $e->getMessage());
        Logger::instance()->dump($e, "Stack trace:");
    }
}
