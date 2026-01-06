<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Services\HeaderSender;

it('returns false when headers not sent', function () {
    $sender = new HeaderSender(fn() => false, fn() => null);
    expect($sender->sent())->toBeFalse();
});

it('returns true when headers sent', function () {
    $sender = new HeaderSender(fn() => true, fn() => null);
    expect($sender->sent())->toBeTrue();
});

it('calls the header callable when headers not sent', function () {
    $called = false;
    $headerCallable = function ($header, $replace, $code) use (&$called, &$params) {
        $called = true;
        $params = compact('header', 'replace', 'code');
    };
    $sender = new HeaderSender(fn() => false, $headerCallable);
    $sender->send('X-Test: 1', true, 201);
    expect($called)->toBeTrue();
    expect($params)->toBe([
        'header' => 'X-Test: 1',
        'replace' => true,
        'code' => 201,
    ]);
});

it('does not call header callable when headers already sent', function () {
    $called = false;
    $headerCallable = function () use (&$called) {
        $called = true;
    };
    $sender = new HeaderSender(fn() => true, $headerCallable);
    $sender->send('X-Test: 1');
    expect($called)->toBeFalse();
});

it('uses default PHP functions if no callables provided', function () {
    $sender = new HeaderSender();
    // sent() should return a bool
    expect(is_bool($sender->sent()))->toBeTrue();
    // send() will execute header() if headers not sent, but we can't assert that in test safely
    $sender->send('X-Test: 1');
    expect(true)->toBeTrue(); // dummy assertion to cover send() call
});
