<?php

namespace PHPLedger\Storage\MySql;

use mysqli;
use mysqli_sql_exception;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;
use RuntimeException;

class MySqlConnectionManager
{
    private ?mysqli $connection = null;

    public function getConnection(): mysqli
    {
        if (!$this->connection instanceof mysqli || $this->connection->connect_errno) {
            $this->connect();
        }
        return $this->connection;
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

            if ($this->connection instanceof mysqli) {
                if ($this->connection->connect_errno) {
                    $this->connection->close();
                    $this->connection = null;
                } else {
                    return;
                }
            }

            $this->connection = new mysqli($host, $user, $pass, $dbase);

            if ($ssl) {
                Logger::instance()->info("Establishing MySQL SSL connection");
                mysqli_ssl_set($this->connection, null, null, null, null, null);
            }

            $this->connection->set_charset('utf8mb4');

            Logger::instance()->debug("MySQL connection established");
            Logger::instance()->debug("Host: {$host}, Database: {$dbase}");
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        } finally {
            mysqli_report(MYSQLI_REPORT_OFF);
        }
    }

    public function closeConnection(): void
    {
        if ($this->connection instanceof mysqli) {
            $this->connection->close();
            $this->connection = null;
            Logger::instance()->debug("MySQL connection closed");
        }
    }
}
