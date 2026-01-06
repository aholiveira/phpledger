<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Services\TimezoneService;

beforeEach(function () {
    $_SESSION = [];
    $_COOKIE = [];
    $this->service = new TimezoneService();
});

it('applies default timezone if nothing is set', function () {
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('Europe/Lisbon');
    expect(date_default_timezone_get())->toBe('Europe/Lisbon');
});

it('falls back to UTC if default is invalid', function () {
    $tz = $this->service->apply('Invalid/Zone');
    expect($tz)->toBe('UTC');
    expect(date_default_timezone_get())->toBe('UTC');
});

it('applies timezone from session if valid', function () {
    $_SESSION['timezone'] = 'America/New_York';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('America/New_York');
    expect(date_default_timezone_get())->toBe('America/New_York');
});

it('applies timezone from cookie if session not set and cookie valid', function () {
    $_COOKIE['timezone'] = 'Asia/Tokyo';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('Asia/Tokyo');
    expect(date_default_timezone_get())->toBe('Asia/Tokyo');
    expect($_SESSION['timezone'])->toBe('Asia/Tokyo');
});

it('ignores invalid cookie timezone and uses default', function () {
    $_COOKIE['timezone'] = 'Invalid/Zone';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('Europe/Lisbon');
    expect(date_default_timezone_get())->toBe('Europe/Lisbon');
    expect($_SESSION['timezone'] ?? null)->toBeNull();
});

it('uses session even if cookie is valid', function () {
    $_SESSION['timezone'] = 'America/Los_Angeles';
    $_COOKIE['timezone'] = 'Asia/Tokyo';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('America/Los_Angeles');
    expect(date_default_timezone_get())->toBe('America/Los_Angeles');
});

it('falls back to UTC if session invalid and default invalid', function () {
    $_SESSION['timezone'] = 'Invalid/Zone';
    $tz = $this->service->apply('Invalid/Zone');
    expect($tz)->toBe('UTC');
    expect(date_default_timezone_get())->toBe('UTC');
});

it('falls back to default if session invalid but default valid', function () {
    $_SESSION['timezone'] = 'Invalid/Zone';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($tz)->toBe('Europe/Lisbon');
    expect(date_default_timezone_get())->toBe('Europe/Lisbon');
});

it('does not overwrite session if already valid', function () {
    $_SESSION['timezone'] = 'Europe/Berlin';
    $_COOKIE['timezone'] = 'Asia/Tokyo';
    $tz = $this->service->apply('Europe/Lisbon');
    expect($_SESSION['timezone'])->toBe('Europe/Berlin');
    expect($tz)->toBe('Europe/Berlin');
});
