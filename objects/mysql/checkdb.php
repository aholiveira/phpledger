<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class check_db implements icheck_storage
{
    private $_dblink;
    public string $message;
    private config $_config;
    private $_tableCreateSQL;
    public function __construct(config $config)
    {
        $this->_config = $config;
        $this->message = "";
        $host = $config->getParameter("host");
        $user = $config->getParameter("user");
        $pass = $config->getParameter("password");
        $dbase = $config->getParameter("database");
        $this->_dblink = new mysqli($host, $user, $pass, $dbase) or die(mysqli_connect_error());
        $this->setTableCreateSQL();
    }
    private function addMessage(string $message)
    {
        $this->message .= "{$message}</br>\r\n";
    }
    public function check(): bool
    {
        $retval = true;
        $tables = array_keys($this->_tableCreateSQL);
        foreach ($tables as $table_name) {
            if (!$this->tableExists($table_name)) {
                $this->addMessage("Table {$table_name} does not exist");
                $retval = false;
            }
            if ($this->getTableEngine($table_name) != "InnoDB") {
                $this->addMessage("Table {$table_name} engine not InnoDB");
                $retval = false;
            }
            foreach (array_keys($this->_tableCreateSQL[$table_name]['columns']) as $column) {
                if (!$this->tableHasColumn($table_name, $column)) {
                    $this->addMessage("Table {$table_name} missing column {$column}");
                    $retval = false;
                }
            }
        }
        if (!$this->tableHasForeignKey("tipo_mov", "parent_id")) {
            $this->addMessage("Table tipo_mov foreign key parent_id missing");
            $retval = false;
        }
        $entry_category = new entry_category($this->_dblink);
        $entry_category->getById(0);
        if ($entry_category->id !== 0) {
            $this->addMessage("Category '0' does not exist");
            $retval = false;
        }
        $entry_list = $entry_category->getAll(array('parent_id' => array('operator' => 'is', 'value' => 'null'), 'tipo_id' => array('operator' => '>', 'value' => '0')));
        if (sizeof($entry_list) > 0) {
            $this->addMessage("Table tipo_mov needs update");
            $retval = false;
        }
        try {
            $user = new user($this->_dblink);
            if (sizeof($user->getAll()) == 0) {
                $this->addMessage("Table users is empty");
                $retval = false;
            }
        } catch (Exception $ex) {
            $this->addMessage("Table users needs update");
            $retval = false;
        }
        $ledger = new ledger($this->_dblink);
        if (sizeof($ledger->getAll()) == 0) {
            $this->addMessage("Table ledgers is empty");
            $retval = false;
        }
        return $retval;
    }
    public function update(): bool
    {
        $retval = true;
        $tables = array_keys($this->_tableCreateSQL);
        foreach ($tables as $table_name) {
            if ($this->tableExists($table_name)) {
                if (!$this->createTable($table_name)) {
                    $this->addMessage("Failed to create {$table_name}");
                    $retval = false;
                }
            }
            if ($this->getTableEngine($table_name) != "InnoDB") {
                if (!$this->setTableEngine($table_name, "InnoDB")) {
                    $this->addMessage("Could not change engine on table {$table_name}");
                    $retval = false;
                }
            }
            foreach ($this->_tableCreateSQL[$table_name]['columns'] as $column_name => $column_definition) {
                if (!$this->tableHasColumn($table_name, $column_name)) {
                    $definition = $column_definition . (isset($last_column) ? " AFTER {$last_column}" : "");
                    if (!$this->addColumnToTable($column_name, $table_name, $definition)) {
                        $this->addMessage("Could not add column {$column_name} to table {$table_name}");
                        $retval = false;
                    }
                }
                $last_column = $column_name;
            }
        }
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
        $entry_category = new entry_category($this->_dblink);
        $entry_category->getById(0);
        if ($entry_category->id !== 0) {
            $entry_category->id = 0;
            $entry_category->description = "Sem categoria";
            $entry_category->parent_id = null;
            $entry_category->active = 1;
            $entry_category->save();
            $sql = "update tipo_mov set parent_id=0 where parent_id is null and tipo_id not in (select parent_id from tipo_mov where parent_id is not null group by parent_id) and tipo_id > 0";
            if (!$this->do_query($sql)) {
                $this->addMessage("Could not update categories");
                $retval = false;
            }
        }
        if (!$this->do_query("update tipo_mov set parent_id=0 where tipo_id>0 and parent_id is null")) {
            $this->addMessage("Could not update categories with null parent");
            $retval = false;
        }
        $user = new user($this->_dblink);
        if (sizeof($user->getAll()) == 0) {
            $user->setId(1);
            $user->setUsername('admin');
            $user->setPassword('admin');
            $user->setFullName('Default admin');
            $user->setEmail('');
            $user->setRole(1);
            $user->setToken('');
            $user->setTokenExpiry('');
            $user->setActive(1);
            if (!$user->save()) {
                $this->addMessage("Could not add user admin");
                $retval = false;
            }
        }
        $ledger = new ledger($this->_dblink);
        if (sizeof($ledger->getAll()) == 0) {
            $ledger->setId(1);
            $ledger->name = "Default";
            if (!$ledger->save()) {
                $this->addMessage("Could not add default ledger");
                $retval = false;
            }
        }
        return $retval;
    }
    public function populateRandomData()
    {
        global $object_factory;
        $category = $object_factory->entry_category();
        $ledger_entry = $object_factory->ledgerentry();
        $start_year = date("Y") - 3;
        $end_year = date("Y");
        $max_month_entries = 100;
        $account = $object_factory->account();
        $account_list = $account->getAll(array('activa' => array('operator' => '=', 'value' => '1')));
        $category_list = $category->getAll(array('active' => array('operator' => '=', 'value' => '1')));
        if (array_key_exists(0, $category_list)) {
            unset($category_list[0]);
        }

        $months = date_diff(new DateTime(date("Y-m-d")), new DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $start_year))));
        for ($year = $start_year; $year <= $end_year; $year++) {
            for ($month = 1; $month <= ($year == date("Y") ? date("m") : 12); $month++) {
                $days_in_month = ($year == date("Y") && $month == date("m") ? date("d") : cal_days_in_month(CAL_GREGORIAN, $month, $year));
                $entries_to_create = rand(round(0.7 * $max_month_entries, 0), $max_month_entries);
                for ($entry_counter = 1; $entry_counter <= $entries_to_create; $entry_counter++) {
                    $ledger_entry->id = $ledger_entry->getFreeId();
                    $ledger_entry->account_id = $account_list[array_rand($account_list)]->id;
                    $ledger_entry->category_id = $category_list[array_rand($category_list)]->id;
                    if ($ledger_entry->category_id == 0) exit(0);
                    $ledger_entry->entry_date = date("Y-m-d", mktime(0, 0, 0, $month, rand(1, $days_in_month), $year));
                    $ledger_entry->direction =  array(-1, 1)[array_rand(array(-1, 1))];
                    $ledger_entry->currency_amount = rand(1, 100000) / 100;
                    $ledger_entry->currency_id = 'EUR';
                    $ledger_entry->euro_amount = $ledger_entry->currency_amount * $ledger_entry->direction;
                    $ledger_entry->exchange_rate = 1;
                    $ledger_entry->save();
                }
                $curr_month = date_diff(new DateTime(date("Y-m-d", mktime(0, 0, 0, $month, 1, $year))), new DateTime(date("Y-m-d", mktime(0, 0, 0, 1, 1, $start_year))));
                print number_format(($curr_month->y * 12 + $curr_month->m) / ($months->y * 12 + $curr_month->m + 1) * 100, 1) . "%\r\n";
            }
        }
    }
    private function do_query_get_result($sql)
    {
        $retval = null;
        if (!is_object($this->_dblink)) return $retval;
        try {
            $stmt = @$this->_dblink->prepare($sql);
            if ($stmt == false) return $retval;
            $stmt->execute();
            $stmt->bind_result($retval);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $ex) {
            print_var($ex, "", true);
        }
        return $retval;
    }
    private function do_query($sql)
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        try {
            $stmt = $this->_dblink->prepare($sql);
            if ($stmt == false) throw new mysqli_sql_exception();
            $retval = $stmt->execute();
            $stmt->close();
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function tableExists(string $table_name): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}'";
        try {
            $count = $this->do_query_get_result($sql);
            $retval = ($count == 1);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function addColumnToTable(string $column_name, string $table_name, string $typedef): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "SELECT count(*) as colCount FROM information_schema.columns WHERE table_name = '{$table_name}' AND column_name = '{$column_name}' and table_schema = DATABASE()";
        try {
            if ($this->tableHasColumn($table_name, $column_name)) return true;
            $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` $typedef";
            $retval = $this->do_query($sql);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function tableHasForeignKey(string $table_name, string $key_name): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "SELECT count(*) as colCount FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table_name}' AND CONSTRAINT_NAME='{$key_name}'";
        try {
            $count = $this->do_query_get_result($sql);
            $retval = ($count == 1);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function addForeignKeyToTable(string $key_name, string $fk_def, string $table_name): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "ALTER TABLE `{$table_name}` ADD FOREIGN KEY `{$key_name}` (`{$key_name}`) REFERENCES {$fk_def}";
        try {
            if ($this->tableHasForeignKey($table_name, $key_name)) return true;
            $retval = $this->do_query($sql);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function tableHasColumn(string $table_name, string $column_name): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "SELECT count(*) as colCount FROM information_schema.columns WHERE table_name = '{$table_name}' AND column_name = '{$column_name}' and table_schema = DATABASE()";
        try {
            $count = $this->do_query_get_result($sql);
            $retval = ($count == 1);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function getTableEngine(string $table_name): string
    {
        $retval = "";
        if (!is_object($this->_dblink)) return $retval;
        $sql = "SELECT ENGINE FROM information_schema.TABLES WHERE table_name = '{$table_name}' AND table_schema = DATABASE()";
        try {
            $retval = $this->do_query_get_result($sql);
        } catch (Exception $ex) {
            print_var($ex, "", true);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function setTableEngine(string $table_name, string $engine): bool
    {
        $retval = false;
        if (!is_object($this->_dblink)) return $retval;
        $sql = "ALTER TABLE `{$table_name}` ENGINE={$engine}";
        try {
            $retval = $this->do_query($sql);
        } catch (Exception $ex) {
            print_var($ex, "", false);
            print_var($this->_dblink, "", false);
        }
        return $retval;
    }
    private function createTable(string $table_name)
    {
        $retval = true;
        if (!$this->tableExists($table_name)) {
            if (array_key_exists($table_name, $this->_tableCreateSQL)) {
                $sql = "CREATE TABLE `{$table_name}` (";
                foreach ($this->_tableCreateSQL['columns'] as $column_name => $column_definition) {
                    $sql .= "`{$column_name}` {$column_definition},";
                }
                $sql .= "PRIMARY KEY (`{$this->_tableCreateSQL[$table_name]['primary_key']}`)";
                if (array_key_exists('keys', $this->_tableCreateSQL[$table_name])) {
                    $sql .= ",";
                    foreach ($this->_tableCreateSQL[$table_name]['keys'] as $key_name => $key_def) {
                        $sql .= "KEY `{$key_name}` {$key_def},";
                    }
                }
                if (array_key_exists('constaints', $this->_tableCreateSQL[$table_name])) {
                    foreach ($this->_tableCreateSQL[$table_name]['constaints'] as $key_name => $key_def) {
                        $sql .= "CONSTRAINT `{$key_name}` FOREIGN KEY (`{$key_name}`) REFERENCES {$key_def}";
                    }
                }
                $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1";
                $retval = $this->do_query($sql);
            }
        }
        return $retval;
    }
    private function setTableCreateSQL()
    {

        $this->_tableCreateSQL['contas']['columns'] = array(
            "conta_id" => "int(3) NOT NULL DEFAULT 0",
            "conta_num" => "char(30) NOT NULL DEFAULT ''",
            "conta_nome" => "char(30) NOT NULL DEFAULT ''",
            "grupo" => "int(3) NOT NULL DEFAULT 0",
            "tipo_id" => "int(2) DEFAULT NULL",
            "conta_nib" => "char(24) DEFAULT NULL",
            "conta_abertura" => "date DEFAULT NULL",
            "conta_fecho" => "date DEFAULT NULL",
            "activa" => "int(1) NOT NULL DEFAULT 0"
        );
        $this->_tableCreateSQL['contas']['primary_key'] = "conta_id";

        $this->_tableCreateSQL['defaults']['columns'] = array(
            "id" => "int(1) NOT NULL DEFAULT 0",
            "tipo_mov" => "int(3) DEFAULT NULL",
            "conta_id" => "int(3) DEFAULT NULL",
            "moeda_mov" => "char(3) DEFAULT NULL",
            "data" => "date DEFAULT NULL",
            "deb_cred" => "enum('1','-1') DEFAULT NULL"
        );
        $this->_tableCreateSQL['defaults']['primary_key'] = "id";

        $this->_tableCreateSQL['grupo_contas']['columns'] = array(
            "id" => "int(4) NOT NULL DEFAULT 0",
            "nome" => "char(30) NOT NULL DEFAULT ''"
        );
        $this->_tableCreateSQL['grupo_contas']['primary_key'] = "id";

        $this->_tableCreateSQL['moedas']['columns'] = array(
            "moeda_id" => "char(3) NOT NULL DEFAULT ''",
            "moeda_desc" => "char(30) DEFAULT NULL",
            "taxa" => "float(8,6) DEFAULT NULL"
        );
        $this->_tableCreateSQL['moedas']['primary_key']  = "moeda_id";

        $this->_tableCreateSQL['movimentos']['columns'] = array(
            "mov_id" => "int(4) NOT NULL AUTO_INCREMENT",
            "data_mov" => "date DEFAULT NULL",
            "tipo_mov" => "int(3) DEFAULT NULL",
            "conta_id" => "int(3) DEFAULT NULL",
            "moeda_mov" => "char(3) NOT NULL DEFAULT 'EUR'",
            "deb_cred" => "enum('1','-1') NOT NULL DEFAULT '1'",
            "valor_mov" => "float(10,2) DEFAULT NULL",
            "valor_euro" => "float(10,2) DEFAULT NULL",
            "cambio" => "float(9,4) NOT NULL DEFAULT 1.0000",
            "a_pagar" => "tinyint(1) NOT NULL DEFAULT 0",
            "com_talao" => "tinyint(1) NOT NULL DEFAULT 0",
            "obs" => "char(255) DEFAULT NULL",
            "username" => "char(255) DEFAULT ''",
            "last_modified" => "TIMESTAMP"
        );
        $this->_tableCreateSQL['movimentos']['primary_key'] = "mov_id";

        $this->_tableCreateSQL['tipo_contas']['columns'] = array(
            "tipo_id" => "int(2) NOT NULL DEFAULT 0",
            "tipo_desc" => "char(30) DEFAULT NULL",
            "savings" => "int(1) NOT NULL DEFAULT 0"
        );
        $this->_tableCreateSQL['tipo_contas']['primary_key'] = "tipo_id";

        $this->_tableCreateSQL['tipo_mov']['columns'] = array(
            "tipo_id" => "int(3) NOT NULL DEFAULT 0",
            "parent_id" => "int(3) DEFAULT NULL",
            "tipo_desc" => "char(50) DEFAULT NULL",
            "active" => "int(1) NOT NULL DEFAULT 0"
        );
        $this->_tableCreateSQL['tipo_mov']['primary_key'] = "tipo_id";
        $this->_tableCreateSQL['tipo_mov']['keys'] = array("parent_id"  => "parent_id");
        $this->_tableCreateSQL['tipo_mov']['constaints'] = array("parent_id" => "`tipo_mov` (`tipo_id`) ON DELETE CASCADE ON UPDATE CASCADE");

        $this->_tableCreateSQL['users']['columns'] = array(
            "id" => "int(3) NOT NULL DEFAULT 0",
            "username" => "char(100) NOT NULL",
            "password" => "char(255) NOT NULL",
            "fullname" => "char(255) NOT NULL DEFAULT ''",
            "email" => "char(255) NOT NULL DEFAULT ''",
            "role" => "int(3) NOT NULL DEFAULT 0",
            "token" => "char(255) NOT NULL DEFAULT ''",
            "token_expiry" => "datetime",
            "active" => "int(1) NOT NULL DEFAULT 0"
        );
        $this->_tableCreateSQL['users']['primary_key'] = "id";
    }
}
