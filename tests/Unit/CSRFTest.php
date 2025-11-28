<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\CSRF;

beforeEach(function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $_SESSION = [];
});

afterEach(function () {
    $_SESSION = [];
});

it('generates a token and stores it in session', function () {
    $token = CSRF::generateToken();
    expect($token)->toBeString();
    expect($_SESSION['_csrf_token']['value'])->toBe($token);
});

it('gets a valid token from session', function () {
    $token = CSRF::generateToken();
    expect(CSRF::getToken())->toBe($token);
});

it('returns null when no token exists', function () {
    expect(CSRF::getToken())->toBeNull();
});

it('returns null when token is expired', function () {
    CSRF::generateToken();
    $_SESSION['_csrf_token']['time'] = time() - 10;
    expect(CSRF::getToken())->toBeNull();
    expect($_SESSION)->toBeEmpty();
});

it('validates the correct token and invalidates afterward', function () {
    $token = CSRF::generateToken();
    expect(CSRF::validateToken($token))->toBeTrue();
    expect($_SESSION)->toBeEmpty();
});

it('fails validation for wrong token', function () {
    CSRF::generateToken();
    expect(CSRF::validateToken('wrong'))->toBeFalse();
});

it('fails validation when no token provided', function () {
    CSRF::generateToken();
    expect(CSRF::validateToken(null))->toBeFalse();
});

it('removes token from session', function () {
    CSRF::generateToken();
    CSRF::removeToken();
    expect($_SESSION)->toBeEmpty();
});

it('generates input field with existing token', function () {
    $token = CSRF::generateToken();
    $html = CSRF::inputField();
    expect($html)->toContain($token);
});

it('generates input field and token when none exists', function () {
    $html = CSRF::inputField();
    expect($html)->toContain('_csrf_token');
    expect($_SESSION['_csrf_token']['value'])->toBeString();
});
