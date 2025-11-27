<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\L10n;

beforeEach(function () {
    if (!\defined('ROOT_DIR')) {
        define('ROOT_DIR', __DIR__);
    }
    $_SESSION = [];
    $_REQUEST = [];
    $_SERVER = [];
});

it('sets forced language when init is called', function () {
    L10n::$forcedLang = 'en-us';
    L10n::init();
    expect(L10n::$lang)->toBe('en-us');
});

it('detects user language from request', function () {
    $_REQUEST['lang'] = 'pt-pt';
    L10n::$forcedLang = null;
    L10n::init();
    expect(L10n::$lang)->toBe('pt-pt');
});

it('detects user language from browser header', function () {
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
    $_REQUEST['lang'] = '';
    L10n::$forcedLang = null;
    L10n::init();
    expect(L10n::$lang)->toBe('en-us');
});

it('returns default language if unknown', function () {
    $_REQUEST['lang'] = '';
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR';
    L10n::$forcedLang = null;
    L10n::init();
    expect(L10n::$lang)->toBe('pt-pt');
});

it('returns HTML language code', function () {
    L10n::$lang = 'en-us';
    expect(L10n::html())->toBe('en-US');

    L10n::$lang = 'pt-pt';
    expect(L10n::html())->toBe('pt-PT');

    L10n::$lang = 'other';
    expect(L10n::html())->toBe('pt-PT');
});

it('returns translation for a given id', function () {
    $ref = new \ReflectionClass(L10n::class);
    $prop = $ref->getProperty('l10n');
    $prop->setValue(null, ['greet' => 'Hello %s']);
    $result = L10n::l('greet', 'World');
    expect($result)->toBe(htmlspecialchars('Hello World', ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', false));
});

it('returns empty string for missing translation', function () {
    $ref = new \ReflectionClass(L10n::class);
    $prop = $ref->getProperty('l10n');
    $prop->setValue(null, []);
    expect(L10n::l('missing'))->toBe('');
});
