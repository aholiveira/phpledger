<?php

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
