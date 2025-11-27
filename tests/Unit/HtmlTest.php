<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\Html;
use PHPLedger\Util\Config;
use PHPLedger\Util\L10n;

if (!\defined('PHPLedger\Util\VERSION')) {
    define('PHPLedger\Util\VERSION', 'TestApp');
}
if (!\defined('PHPLedger\Util\ROOT_DIR')) {
    define('PHPLedger\Util\ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
}

beforeEach(function () {
    ini_set('short_open_tag', '1');
    // Setup dummy config and language
    Config::set('title', 'TestApp');
    L10n::init();
    L10n::$lang = 'pt-pt';
    if (!isset($_SESSION)) {
        session_start();
        $_SESSION = [];
    }
    $_SESSION['expires'] = time() + 3600;
});

it('builds year options with selected year', function () {
    $output = Html::yearOptions(2024, 2020, 2025);
    expect(str_contains($output, '<option value="2024" selected>2024</option>'))->toBeTrue();
});

it('builds month options with selected month', function () {
    $output = Html::monthOptions('5');
    expect(str_contains($output, '<option value="5" selected>05</option>'))->toBeTrue();
});

it('builds day options with selected day', function () {
    $output = Html::dayOptions('15');
    expect(str_contains($output, '<option value="15" selected>15</option>'))->toBeTrue();
});

it('builds hour options with selected hour', function () {
    $output = Html::hourOptions('12');
    expect(str_contains($output, '<option value="12" selected>12</option>'))->toBeTrue();
});

it('builds minute options with selected minute', function () {
    $output = Html::minuteOptions('30');
    expect(str_contains($output, '<option value="30" selected>30</option>'))->toBeTrue();
});

it('builds generic options', function () {
    $output = Html::buildOptions(1, 3, '2');
    expect(str_contains($output, '<option value="2" selected>2</option>'))->toBeTrue();
});

it('renders errortext without exit', function () {
    ob_start();
    Html::errortext('Error message', false);
    $output = ob_get_clean();
    expect(str_contains($output, 'Error message'))->toBeTrue();
});

it('renders myalert javascript', function () {
    ob_start();
    Html::myalert('Hello alert');
    $output = ob_get_clean();
    expect(str_contains($output, 'alert("Hello alert")'))->toBeTrue();
});

it('renders header html with page title', function () {
    ob_start();
    Html::header('My Page');
    $output = ob_get_clean();
    expect(str_contains($output, '<title>My Page - TestApp</title>'))->toBeTrue();
});

it('renders footer html with session expiration', function () {
    ob_start();
    Html::footer();
    $output = ob_get_clean();
    expect(str_contains($output, '<footer>'))->toBeTrue();
    expect(str_contains($output, date("Y-m-d", $_SESSION['expires'])))->toBeTrue();
});

it('renders menu html with language links', function () {
    ob_start();
    Html::menu();
    $output = ob_get_clean();
    expect(str_contains($output, '<aside class="menu">'))->toBeTrue();
    expect(str_contains($output, "?lang=" . L10n::$lang))->toBeTrue();
});

it('renders language selector in div', function () {
    ob_start();
    Html::languageSelector(true);
    $output = ob_get_clean();
    expect(str_contains($output, '<div>'))->toBeTrue();
    expect(str_contains($output, 'EN</a> | <span>PT'))->toBeTrue();
});

it('renders language selector without div', function () {
    ob_start();
    Html::languageSelector(false);
    $output = ob_get_clean();
    expect(str_contains($output, '<div>'))->toBeFalse();
    expect(str_contains($output, 'EN</a> | <span>PT'))->toBeTrue();
});
