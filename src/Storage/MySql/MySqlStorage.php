<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Storage\MySql;

use mysqli;
use PHPLedger\Contracts\DataStorageInterface;
use PHPLedger\Services\Config;
use Throwable;

class MySqlStorage implements DataStorageInterface
{
    private ?mysqli $dbConnection = null;
    private string $message = "";
    private static ?self $instance = null;
    public function __construct()
    {
        $this->message = "";
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
                    } catch (Throwable) {
                        // Ignore errors on closing a broken connection
                    }
                    $this->dbConnection = null;
                } else {
                    return;
                }
            }
            $this->dbConnection = new mysqli($host, $user, $pass, $dbase);
            if ($ssl) {
                mysqli_ssl_set($this->dbConnection, null, null, null, null, null);
            }
            $this->dbConnection->set_charset('utf8mb4');
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
}
