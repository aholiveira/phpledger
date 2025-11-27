<?php

use PHPLedger\Util\SessionManager;
use PHPLedger\Util\L10n;

beforeEach(function () {
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    $_SESSION = [];
    $_SERVER['SCRIPT_NAME'] = 'index.php';
    L10n::$lang = 'en';
});

it('refreshExpiration updates TTL', function () {
    SessionManager::start();
    $ttl = 1000;
    SessionManager::refreshExpiration($ttl);

    expect($_SESSION['expires'])
        ->toBeGreaterThan(time() + $ttl - 3)
        ->toBeLessThan(time() + $ttl + 3);
});

it('guard redirects when not logged in and not public', function () {
    $_SERVER['SCRIPT_NAME'] = 'dashboard.php';
    SessionManager::start();
    $_SESSION['expires'] = time() + 200;
    expect(SessionManager::guard(['index.php'], 300))->toBeFalse();
});

it('guard refreshes TTL for authenticated user', function () {
    $_SERVER['SCRIPT_NAME'] = 'dashboard.php';

    SessionManager::start();
    $_SESSION['user'] = 'demo';
    $_SESSION['expires'] = time() + 10;

    SessionManager::guard(['index.php'], 500);

    expect($_SESSION['expires'])
        ->toBeGreaterThan(time() + 495);
});

it('starts a session with the correct name', function () {
    SessionManager::start();
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);
    expect(session_name())->toBe('phpledger_session');
});

it('returns true when session is expired', function () {
    SessionManager::start();
    $_SESSION['expires'] = time() - 50;
    expect(SessionManager::isExpired())->toBeTrue();
});

it('returns false when session is not expired', function () {
    SessionManager::start();
    $_SESSION['expires'] = time() + 200;
    expect(SessionManager::isExpired())->toBeFalse();
});

it('logout clears session data', function () {
    SessionManager::start();
    $_SESSION['foo'] = 'bar';
    SessionManager::logout();
    expect($_SESSION)->toBeEmpty();
});
