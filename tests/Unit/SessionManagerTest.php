<?php

use PHPLedger\Util\L10n;
use PHPLedger\Util\SessionManager;

beforeEach(function () {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    $_SERVER['SCRIPT_NAME'] = 'index.php';
});

it('refreshExpiration updates TTL', function () {
    $session = new SessionManager();
    $session->start();
    $ttl = 1000;
    $session->refreshExpiration($ttl);

    expect($session->get('expires'))
        ->toBeGreaterThan(time() + $ttl - 3)
        ->toBeLessThan(time() + $ttl + 3);
});


it('starts a session with the correct name', function () {
    $session = new SessionManager();
    $session->start();
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);
    expect(session_name())->toBe('phpledger_session');
});

it('returns true when session is expired', function () {
    $session = new SessionManager();
    $session->start();
    $session->set('expires', time() - 50);
    expect($session->isExpired())->toBeTrue();
});

it('returns false when session is not expired', function () {
    $session = new SessionManager();
    $session->start();
    $session->set('expires', time() + 200);
    expect($session->isExpired())->toBeFalse();
});

it('logout clears session data', function () {
    $session = new SessionManager();
    $session->start();
    $session->set('foo', 'bar');
    $session->logout();
    expect($_SESSION)->toBeEmpty();
});
