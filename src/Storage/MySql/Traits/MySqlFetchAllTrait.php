<?php

namespace PHPLedger\Storage\MySql\Traits;

use Exception;
use mysqli_sql_exception;
use PHPLedger\Storage\MySql\MySqlStorage;

trait MySqlFetchAllTrait
{
    private static function fetchAll(string $sql, array $params = []): array
    {
        $retval = [];
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        if ($stmt === false) {
            throw new mysqli_sql_exception();
        }
        if ($params) {
            $types = str_repeat('s', \count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($obj = $result->fetch_object(__CLASS__)) {
            $retval[$obj->id] = $obj;
        }
        $stmt->close();
        return $retval;
    }
}
