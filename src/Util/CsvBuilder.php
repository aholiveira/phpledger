<?php

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
