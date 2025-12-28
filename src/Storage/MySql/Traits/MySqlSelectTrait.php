<?php

namespace PHPLedger\Storage\MySql\Traits;

/**
 * Trait providing a standard getSelect() method for MySql storage classes.
 * Builds a SELECT statement with backticked column names from getDefinition().
 */
trait MySqlSelectTrait
{
    private static function getSelect(): string
    {
        $cols = array_values(static::getDefinition());
        $cols = array_map(function ($c) {
            return "`" . $c . "`";
        }, $cols);
        return "SELECT " . implode(", ", $cols) . " FROM `" . static::tableName() . "`";
    }
}
