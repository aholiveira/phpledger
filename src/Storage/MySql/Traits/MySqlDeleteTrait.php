<?php

namespace PHPLedger\Storage\MySql\Traits;

use Exception;
use PHPLedger\Storage\MySql\MySqlStorage;

trait MySqlDeleteTrait
{
    public function delete(): bool
    {
        $retval = false;
        try {
            $sql = "DELETE FROM {$this->tableName()} WHERE id=?";
            $stmt = MySqlStorage::getConnection()->prepare($sql);
            $stmt->bind_param("s", $this->id);
            $retval = $stmt->execute();
            $stmt->close();
        } catch (Exception $ex) {
            $this->handleException($ex, $sql);
        }
        return $retval;
    }
}
