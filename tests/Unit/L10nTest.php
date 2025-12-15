<?php

use PHPLedger\Util\L10n;
use PHPLedger\Util\Path;

beforeEach(function () {
    if (!\defined('ROOT_DIR')) {
        define('ROOT_DIR', __DIR__);
    }
    $_SESSION = [];
    $_REQUEST = [];
    $_SERVER = [];
});

it('initializes with forced language', function () {
    $l10n = new L10n();
    $l10n->setLang('en-us');
    expect($l10n->lang())->toBe('en-us');
});

it('detects user language from request', function () {
    $_REQUEST['lang'] = 'pt-pt';
    $l10n = new L10n();
    expect($l10n->lang())->toBe('pt-pt');
});

it('detects user language from browser header', function () {
    $_REQUEST['lang'] = '';
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
    $l10n = new L10n();
    expect($l10n->lang())->toBe('en-us');
});

it('returns default language if unknown', function () {
    $_REQUEST['lang'] = '';
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR';
    $l10n = new L10n();
    expect($l10n->lang())->toBe('pt-pt');
});

it('returns HTML language code', function () {
    $l10n = new L10n();

    $l10n->setLang('en-us');
    expect($l10n->html())->toBe('en-US');

    $l10n->setLang('pt-pt');
    expect($l10n->html())->toBe('pt-PT');

    $l10n->setLang('other');
    expect($l10n->html())->toBe('pt-PT');
});

it('returns translation for a given id with replacements', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $prop = $ref->getProperty('l10n');
    $prop->setAccessible(true);
    $prop->setValue($l10n, ['greet' => 'Hello %s']);
    $result = $l10n->l('greet', 'World');
    expect($result)->toBe(htmlspecialchars('Hello World', ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', false));
});

it('returns empty string for missing translation', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $prop = $ref->getProperty('l10n');
    $prop->setAccessible(true);
    $prop->setValue($l10n, []);
    expect($l10n->l('missing'))->toBe('');
});

it('prints translation with pl()', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $prop = $ref->getProperty('l10n');
    $prop->setAccessible(true);
    $prop->setValue($l10n, ['hello' => 'Hi']);
    ob_start();
    $l10n->pl('hello');
    $output = ob_get_clean();
    expect($output)->toBe('Hi');
});

it('sanitizes allowed language codes', function () {
    $l10n = new L10n();
    expect($l10n->sanitizeLang('en'))->toBe('en');
    expect($l10n->sanitizeLang('pt-pt'))->toBe('pt-pt');
    expect($l10n->sanitizeLang('EN-US'))->toBe('EN-US');
    expect($l10n->sanitizeLang('fr'))->toBe($l10n->lang());
});

it('safeSprintf handles mismatched placeholders gracefully', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $method = $ref->getMethod('safeSprintf');
    $method->setAccessible(true);

    $format = 'Hello %s %s';
    $result = $method->invoke($l10n, $format, ['World']);
    expect($result)->toBe('Hello %s %s World');

    $format2 = 'Hello %s';
    $result2 = $method->invoke($l10n, $format2, ['World']);
    expect($result2)->toBe('Hello World');
});

it('normalizeLang converts to lowercase and replaces underscores', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $method = $ref->getMethod('normalizeLang');
    $method->setAccessible(true);

    expect($method->invoke($l10n, 'EN_US'))->toBe('en-us');
    expect($method->invoke($l10n, ' Pt_Pt '))->toBe('pt-pt');
});

it('loads language from cache when available', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $propCache = $ref->getProperty('cache');
    $propCache->setAccessible(true);
    $propCache->setValue($l10n, ['pt-pt' => ['hello' => 'Hi']]);

    $method = $ref->getMethod('loadLang');
    $method->setAccessible(true);
    $result = $method->invoke($l10n, 'pt-pt');

    expect($result)->toBe(['hello' => 'Hi']);
});

it('loadLang falls back to pt-pt if file missing', function () {
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $method = $ref->getMethod('loadLang');
    $method->setAccessible(true);

    $result = $method->invoke($l10n, 'non-existent-lang');
    expect($result)->toBe([]);
});

it('detectUserLang returns normalized lang from request or browser', function () {
    $_REQUEST['lang'] = 'EN-US';
    $l10n = new L10n();
    $ref = new \ReflectionClass($l10n);
    $method = $ref->getMethod('detectUserLang');
    $method->setAccessible(true);

    expect($method->invoke($l10n))->toBe('en-us');

    $_REQUEST = [];
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pt-BR';
    expect($method->invoke($l10n))->toBe('pt-pt');
});

it('loads language from JSON file', function () {
    $langDir = ROOT_DIR . '/lang';
    if (!is_dir($langDir)) mkdir($langDir, 0777, true);

    $file = $langDir . '/en-us.json';
    file_put_contents($file, json_encode(['hello' => 'Hi']));

    $l10n = new L10n();
    $l10n->setLang('en-us');

    $ref = new \ReflectionClass($l10n);
    $prop = $ref->getProperty('l10n');
    $prop->setAccessible(true);

    expect($prop->getValue($l10n))->toBe(['hello' => 'Hi']);

    // cleanup
    unlink($file);
});
