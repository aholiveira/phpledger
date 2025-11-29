<?php

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

it('parses using AA/MM/DD parts', function () {
    $date = DateParser::parseNamed('x', [
        'xAA' => '2023',
        'xMM' => '12',
        'xDD' => '25',
    ]);
    expect($date)->toBeInstanceOf(DateTimeImmutable::class);
    expect($date->format('Y-m-d'))->toBe('2023-12-25');
});

it('throws when AA/MM/DD form an invalid date', function () {
    DateParser::parseNamed('x', [
        'xAA' => '2024',
        'xMM' => '02',
        'xDD' => '31',
    ]);
})->throws(InvalidArgumentException::class);

it('returns null when AA/MM/DD missing completely', function () {
    expect(DateParser::parseNamed('k', ['kAA' => '', 'kMM' => '', 'kDD' => '']))->toBeNull();
});

it('parses when some parts are missing but enough to form a valid date', function () {
    $date = DateParser::parseNamed('a', [
        'aAA' => '2022',
        'aMM' => '01',
        'aDD' => '05',
    ]);
    expect($date)->toBeInstanceOf(DateTimeImmutable::class);
    expect($date->format('Y-m-d'))->toBe('2022-01-05');
});

it('throws when some parts missing result in invalid date', function () {
    DateParser::parseNamed('a', [
        'aAA' => '2022',
        'aMM' => '00',
        'aDD' => '15',
    ]);
})->throws(InvalidArgumentException::class);
