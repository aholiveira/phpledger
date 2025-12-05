<?php

use PHPLedger\Util\PasswordGenerator;

beforeEach(function () {
    // Ensure mbstring behaves consistently
    if (!extension_loaded('mbstring')) {
        $this->markTestSkipped('mbstring required');
    }
});

it('generates a password within length bounds', function () {
    $pwd = PasswordGenerator::generate(8, 12);
    expect(strlen($pwd))->toBeGreaterThanOrEqual(8)
                        ->toBeLessThanOrEqual(12);
});

it('always contains lowercase uppercase digit special', function () {
    $pwd = PasswordGenerator::generate(12, 12);
    expect(preg_match('/[a-z]/', $pwd))->toBe(1);
    expect(preg_match('/[A-Z]/', $pwd))->toBe(1);
    expect(preg_match('/[0-9]/', $pwd))->toBe(1);
    expect(preg_match('/[!@#$%^&*()_+\-=\{}\[\]\|:;"<>,.\?\/]/', $pwd))->toBe(1);
});

it('rejects passwords too similar to given strings', function () {
    // Force loop until similarity < threshold
    $pwd = PasswordGenerator::generate(10, 10, ['AAAAAAAAAA'], 5);
    expect($pwd)->not()->toBe('AAAAAAAAAA');
    expect(similar_text($pwd, 'AAAAAAAAAA') < 5)->toBeTrue();
});

it('respects custom similarity threshold', function () {
    $p1 = PasswordGenerator::generate(6, 6, ['ZZZZZZ'], 10);
    expect(similar_text($p1, 'ZZZZZZ'))->toBeLessThan(10);
});

it('minLength == maxLength enforces fixed size', function () {
    $pwd = PasswordGenerator::generate(16, 16);
    expect(strlen($pwd))->toBe(16);
});
