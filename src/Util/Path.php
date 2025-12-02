<?php

namespace PHPLedger\Util;

final class Path
{
    public static function combine(?string ...$parts): string
    {
        return preg_replace('#/+#', '/', join(DIRECTORY_SEPARATOR, array_map(fn($p) => trim($p, '/\\'), $parts)));
    }
}
