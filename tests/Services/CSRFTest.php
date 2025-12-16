<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Services\CSRF;

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
    $csrf = new CSRF();
    $token = $csrf->generateToken();
    expect($token)->toBeString();
    expect($_SESSION['_csrf_token']['value'])->toBe($token);
});

it('gets a valid token from session', function () {
    $csrf = new CSRF();
    $token = $csrf->generateToken();
    expect($csrf->getToken())->toBe($token);
});

it('returns null when no token exists', function () {
    $csrf = new CSRF();
    expect($csrf->getToken())->toBeNull();
});

it('returns null when token is expired', function () {
    $csrf = new CSRF();
    $csrf->generateToken();
    $_SESSION['_csrf_token']['time'] = time() - 10;
    expect($csrf->getToken())->toBeNull();
    expect($_SESSION)->toBeEmpty();
});

it('validates the correct token and invalidates afterward', function () {
    $csrf = new CSRF();
    $token = $csrf->generateToken();
    expect($csrf->validateToken($token))->toBeTrue();
    expect($_SESSION)->toBeEmpty();
});

it('fails validation for wrong token', function () {
    $csrf = new CSRF();
    $csrf->generateToken();
    expect($csrf->validateToken('wrong'))->toBeFalse();
});

it('fails validation when no token provided', function () {
    $csrf = new CSRF();
    $csrf->generateToken();
    expect($csrf->validateToken(null))->toBeFalse();
});

it('removes token from session', function () {
    $csrf = new CSRF();
    $csrf->generateToken();
    $csrf->removeToken();
    expect($_SESSION)->toBeEmpty();
});

it('generates input field with existing token', function () {
    $csrf = new CSRF();
    $token = $csrf->generateToken();
    $html = $csrf->inputField();
    expect($html)->toContain($token);
});

it('generates input field and token when none exists', function () {
    $csrf = new CSRF();
    $html = $csrf->inputField();
    expect($html)->toContain('_csrf_token');
    expect($_SESSION['_csrf_token']['value'])->toBeString();
});
