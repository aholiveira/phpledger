<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Util;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPLedger\Util\DateParser;

it('parses a valid full date string', function () {
    $date = DateParser::parseNamed('d', ['d' => '2024-05-17']);
    expect($date)->toBeInstanceOf(DateTimeImmutable::class);
    expect($date->format('Y-m-d'))->toBe('2024-05-17');
});

it('throws when full date string is invalid', function () {
    DateParser::parseNamed('d', ['d' => 'invalid-date']);
})->throws(InvalidArgumentException::class);

it('returns null when no date fields exist', function () {
    expect(DateParser::parseNamed('d', []))->toBeNull();
});
