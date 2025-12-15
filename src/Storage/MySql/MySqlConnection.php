<?php

namespace PHPLedger\Storage\MySql;

use mysqli_sql_exception;
use mysqli;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;

final class MySqlConnection
{
    private static ?mysqli $conn = null;

    public static function get(): mysqli
    {
        if (self::$conn instanceof mysqli && self::$conn->connect_errno === 0) {
            return self::$conn;
        }

        $host = Config::get("storage.settings.host", "localhost");
        $user = Config::get("storage.settings.user", "root");
        $pass = Config::get("storage.settings.password", "");
        $db   = Config::get("storage.settings.database", "contas_test");
        $port = Config::get("storage.settings.port", 3306);
        $ssl  = Config::get("storage.settings.ssl", false);

        $host = $port !== null ? "{$host}:{$port}" : $host;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            self::$conn = new mysqli($host, $user, $pass, $db);
            self::$conn->set_charset('utf8mb4');
            if ($ssl) {
                mysqli_ssl_set(self::$conn, null, null, null, null, null);
            }
            Logger::instance()->debug("MySQL connected to {$host}, db={$db}");
            return self::$conn;
        } catch (mysqli_sql_exception $e) {
            throw new PHPLedgerException("DB connection failed: " . $e->getMessage(), $e->getCode(), $e);
        } finally {
            mysqli_report(MYSQLI_REPORT_OFF);
        }
    }
}
