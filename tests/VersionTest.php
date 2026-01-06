<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests;

use PHPLedger\Version;

it('splits version into parts', function () {
    $parts = Version::parts();
    expect(count($parts))->toBe(3);
    foreach ($parts as $part) {
        expect(is_numeric($part))->toBeTrue();
    }
});

it('returns numeric major, minor, patch', function () {
    expect(is_int(Version::major()))->toBeTrue();
    expect(is_int(Version::minor()))->toBeTrue();
    expect(is_int(Version::patch()))->toBeTrue();
});

it('converts version to integer', function () {
    $parts = Version::parts();
    $expected = ($parts[0] * 1_000_000) + ($parts[1] * 1_000) + $parts[2];
    expect(Version::toInt())->toBe($expected);
});

it('isAtLeast works relative to current version', function () {
    $current = Version::string();
    $parts = Version::parts();
    $lower = "{$parts[0]}.{$parts[1]}." . max(0, $parts[2] - 1);
    $higher = "{$parts[0]}.{$parts[1]}." . ($parts[2] + 1);

    expect(Version::isAtLeast($lower))->toBeTrue();
    expect(Version::isAtLeast($current))->toBeTrue();
    expect(Version::isAtLeast($higher))->toBeFalse();
});

it('equals works relative to current version', function () {
    $current = Version::string();
    $parts = Version::parts();
    $different = "{$parts[0]}.{$parts[1]}." . ($parts[2] + 1);

    expect(Version::equals($current))->toBeTrue();
    expect(Version::equals($different))->toBeFalse();
});
