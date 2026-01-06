<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

class ConfigPath
{
    private static string $baseDir = ROOT_DIR;
    public static function setbaseDir(string $baseDir)
    {
        self::$baseDir = $baseDir;
    }
    public static function get(): string
    {
        return Path::combine(self::$baseDir, 'config', 'config.json');
    }
}
