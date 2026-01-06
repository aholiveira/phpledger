<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage\MySql\Traits;

use Exception;
use PHPLedger\Storage\MySql\MySqlStorage;

trait MySqlDeleteTrait
{
    public function delete(): bool
    {
        $retval = false;
        $sql = "DELETE FROM {$this->tableName()} WHERE id=?";
        $stmt = MySqlStorage::getConnection()->prepare($sql);
        $stmt->bind_param("s", $this->id);
        $retval = $stmt->execute();
        $stmt->close();
        return $retval;
    }
}
