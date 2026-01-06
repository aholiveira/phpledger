<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Util\CsvBuilder;

it('generates CSV with headers only', function () {
    $headers = ['ID', 'Name', 'Amount'];
    $rows = [];
    $csv = CsvBuilder::build($headers, $rows);
    $csv = str_replace(["\r\n", "\r"], "\n", $csv);

    $expected = "ID;Name;Amount\n";
    expect($csv)->toBe($expected);
});

it('generates CSV with multiple rows', function () {
    $headers = ['ID', 'Name', 'Amount'];
    $rows = [
        [1, 'Alice', 10.5],
        [2, 'Bob', 20.75],
    ];
    $csv = CsvBuilder::build($headers, $rows);
    $csv = str_replace(["\r\n", "\r"], "\n", $csv);

    $expected = "ID;Name;Amount\n1;Alice;10.5\n2;Bob;20.75\n";
    expect($csv)->toBe($expected);
});

it('uses a custom delimiter', function () {
    $headers = ['ID', 'Name', 'Amount'];
    $rows = [[1, 'Alice', 10.5]];
    $csv = CsvBuilder::build($headers, $rows, ',');
    $csv = str_replace(["\r\n", "\r"], "\n", $csv);

    $expected = "ID,Name,Amount\n1,Alice,10.5\n";
    expect($csv)->toBe($expected);
});

it('escapes special characters', function () {
    $headers = ['ID', 'Name', 'Note'];
    $rows = [[1, 'Alice', 'He said "hello"']];
    $csv = CsvBuilder::build($headers, $rows);
    $csv = str_replace(["\r\n", "\r"], "\n", $csv);

    $expected = "ID;Name;Note\n1;Alice;\"He said \"\"hello\"\"\"\n";
    expect($csv)->toBe($expected);
});
