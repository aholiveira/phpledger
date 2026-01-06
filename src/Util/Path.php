<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

final class Path
{
    public static function combine(?string ...$parts): string
    {
        $parts = array_filter($parts, fn($p) => $p !== null && $p !== '');
        if (empty($parts)) {
            return '';
        }

        $first = array_shift($parts);
        $first = rtrim($first, "/\\");
        $rest = array_map(fn($p) => trim($p, "/\\"), $parts);

        return $first . (count($rest) ? DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $rest) : '');
    }
}
