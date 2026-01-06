<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Util\PasswordManager;

beforeEach(function () {
    // Ensure mbstring behaves consistently
    if (!extension_loaded('mbstring')) {
        $this->markTestSkipped('mbstring required');
    }
});

it('generates a password within length bounds', function () {
    $pwd = PasswordManager::generate(8, 12);
    expect(strlen($pwd))->toBeGreaterThanOrEqual(8)
        ->toBeLessThanOrEqual(12);
});

it('always contains lowercase uppercase digit special', function () {
    $pwd = PasswordManager::generate(12, 12);
    expect(preg_match('/[a-z]/', $pwd))->toBe(1);
    expect(preg_match('/[A-Z]/', $pwd))->toBe(1);
    expect(preg_match('/[0-9]/', $pwd))->toBe(1);
    expect(preg_match('/[!@#$%^&*()_+\-=\{}\[\]\|:;"<>,.\?\/]/', $pwd))->toBe(1);
});

it('rejects passwords too similar to given strings', function () {
    // Force loop until similarity < threshold
    $pwd = PasswordManager::generate(10, 10, ['AAAAAAAAAA'], 5);
    expect($pwd)->not()->toBe('AAAAAAAAAA');
    expect(similar_text($pwd, 'AAAAAAAAAA') < 5)->toBeTrue();
});

it('respects custom similarity threshold', function () {
    $p1 = PasswordManager::generate(6, 6, ['ZZZZZZ'], 10);
    expect(similar_text($p1, 'ZZZZZZ'))->toBeLessThan(10);
});

it('minLength == maxLength enforces fixed size', function () {
    $pwd = PasswordManager::generate(16, 16);
    expect(strlen($pwd))->toBe(16);
});
