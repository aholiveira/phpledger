<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

final class CsvBuilder
{
    public static function build(array $headers, array $rows, string $delimiter = ';'): string
    {
        $fp = fopen('php://temp', 'r+');

        fputcsv($fp, $headers, $delimiter, '"', '\\');

        foreach ($rows as $row) {
            fputcsv($fp, $row, $delimiter, '"', '\\');
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }
}
