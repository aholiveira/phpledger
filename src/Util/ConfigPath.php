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

    public static function ensureMigrated(): void
    {
        $new = self::get();
        $old = Path::combine(self::$baseDir, 'config.json');

        // If new file exists, do nothing
        if (file_exists($new)) {
            return;
        }

        // No old file - nothing to migrate
        if (!file_exists($old)) {
            return;
        }

        $dir = dirname($new);

        // Ensure target directory exists
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Attempt migration (rename is atomic on same filesystem)
        @rename($old, $new);
    }
}
