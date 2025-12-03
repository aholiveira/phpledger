<?php

namespace PHPLedger\Util;

final class Path
{
    public static function combine(?string ...$parts): string
    {
        $first = array_shift($parts);
        $path  = $first . '/' . join('/', array_map(fn($p) => trim($p, "/\\"), $parts));
        return preg_replace('#(?<!^)/+#', '/', $path);
    }
}
