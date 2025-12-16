<?php

namespace PHPLedger\Storage\MySql;

use PHPLedger\Services\Logger;
use Throwable;

class MySqlQueryExecutor
{
    private MySqlConnectionManager $connectionManager;

    public function __construct(MySqlConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Execute a query that returns a single value.
     *
     * @param string $sql
     * @return mixed|null
     */
    public function fetchSingleValue(string $sql)
    {
        $result = null;
        try {
            $stmt = $this->connectionManager->getConnection()->prepare($sql);
            if (!$stmt) {
                return $result;
            }
            $stmt->execute();
            $stmt->bind_result($result);
            $stmt->fetch();
            $stmt->close();
        } catch (Throwable $ex) {
            $this->handleException($ex);
        }
        return $result;
    }

    /**
     * Execute a query that does not return a value.
     *
     * @param string $sql
     * @return bool
     */
    public function executeQuery(string $sql): bool
    {
        try {
            $stmt = $this->connectionManager->getConnection()->prepare($sql);
            if ($stmt === false) {
                return false;
            }
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Throwable $ex) {
            $this->handleException($ex);
            return false;
        }
    }

    private function handleException(Throwable $e): void
    {
        Logger::instance()->error("Query execution exception: " . $e->getMessage());
        Logger::instance()->dump($e, "Stack trace:");
    }
}
