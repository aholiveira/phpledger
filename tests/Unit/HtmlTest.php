<?php
namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\LoggerServiceInterface;
use PHPLedger\Util\Config;
use PHPLedger\Util\Html;
use PHPLedger\Version;

class DummySession implements SessionServiceInterface
{
    private array $data = [];

    public function start(): void {}
    public function isAuthenticated(): bool { return true; }
    public function isExpired(): bool { return false; }
    public function logout(): void {}
    public function refreshExpiration(int $ttl = 3600): void {}
    public function set(string $key, mixed $value): void { $this->data[$key] = $value; }
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
}

class DummyL10n implements L10nServiceInterface
{
    private string $lang;

    public function __construct(string $lang = 'en-us') { $this->lang = $lang; }
    public function html(): string { return ''; }
    public function l(string $translationId, mixed ...$replacements): string
    {
        if ($translationId === 'version' && $replacements) return "v{$replacements[0]}";
        if ($translationId === 'session_expires' && $replacements) return "Expires: {$replacements[0]}";
        return ucfirst($translationId);
    }
    public function lang(): string { return $this->lang; }
    public function pl(string $translationId, mixed ...$replacements): void {}
    public function sanitizeLang(?string $lang): string { return $lang ?? 'en-us'; }
    public function setLang(string $lang): void { $this->lang = $lang; }
}

class DummyConfig implements ConfigurationServiceInterface
{
    private array $data = [];
    public static function init(string $configfile, bool $test = false): bool { return true; }
    public function load(string $configfile, bool $test = false): void {}
    public function set(string|array $setting, mixed $value): void
    {
        if (is_array($setting)) $this->data = array_merge($this->data, $setting);
        else $this->data[$setting] = $value;
    }
    public function get(string $setting, mixed $default = null): mixed { return $this->data[$setting] ?? $default; }
    public function validate(array $data): bool { return true; }
    public function getList(array $list): array { return array_intersect_key($this->data, array_flip($list)); }
    public function save(): void {}
}

class DummyApp implements ApplicationObjectInterface
{
    private DummySession $session;
    private DummyL10n $l10n;
    private DummyConfig $config;

    public function __construct()
    {
        $this->session = new DummySession();
        $this->l10n = new DummyL10n();
        $this->config = new DummyConfig();
        $this->session->set('expires', time() + 3600);
    }

    public function config(): ConfigurationServiceInterface { return $this->config; }
    public function dataFactory(): DataObjectFactoryInterface { return new class{}; }
    public function l10n(): L10nServiceInterface { return $this->l10n; }
    public function logger(): LoggerServiceInterface { return new class{}; }
    public function session(): SessionServiceInterface { return $this->session; }
}


beforeEach(function () {
    Config::set('title', 'TestApp', false);
    if (!isset($_SESSION)) {
        session_start();
        $_SESSION = [];
    }
    $_SESSION['expires'] = time() + 3600;
    $_SESSION['user'] = 'adminuser';
    $this->app = new DummyApp();
    $this->l10n = $this->app->l10n();
});

// Option generation tests
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

// Error and alert rendering
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

// Header
it('renders header HTML', function () {
    ob_start();
    Html::header();
    $output = ob_get_clean();
    expect(str_contains($output, '<meta charset="utf-8">'))->toBeTrue();
    expect(str_contains($output, 'assets/styles.css'))->toBeTrue();
});

// Title
it('returns HTML-escaped title with page prefix', function () {
    $title = Html::title('Page');
    expect($title)->toBe('Page - TestApp');
});

it('returns title without page prefix when empty', function () {
    $title = Html::title('');
    expect($title)->toBe('TestApp');
});

// Footer
it('renders footer in pt-pt with EN link', function () {
    $this->l10n->setLang('pt-pt');
    ob_start();
    Html::footer($this->app, 'ledger_entries');
    $output = ob_get_clean();
    expect(str_contains($output, 'EN</a> | <span>PT</span>'))->toBeTrue();
    expect(str_contains($output, 'Expires:'))->toBeTrue();
});

it('renders footer in en-us with PT link', function () {
    $this->l10n->setLang('en-us');
    ob_start();
    Html::footer($this->app, 'ledger_entries');
    $output = ob_get_clean();
    expect(str_contains($output, '<span>EN</span> | <a href='))->toBeTrue();
    expect(str_contains($output, 'Expires:'))->toBeTrue();
});

// Menu
it('renders menu without admin', function () {
    ob_start();
    Html::menu($this->l10n, false);
    $output = ob_get_clean();
    expect(str_contains($output, 'ledger_entries'))->toBeTrue();
    expect(str_contains($output, 'logout'))->toBeTrue();
    expect(str_contains($output, 'config'))->toBeFalse();
});

it('renders menu with admin', function () {
    ob_start();
    Html::menu($this->l10n, true);
    $output = ob_get_clean();
    expect(str_contains($output, 'config'))->toBeTrue();
});

// Language selector
it('renders language selector with div and pt-pt', function () {
    $this->l10n->setLang('pt-pt');
    $_GET['action'] = 'ledger_entries';
    ob_start();
    Html::languageSelector($this->l10n, true);
    $output = ob_get_clean();
    expect(str_contains($output, '<div>'))->toBeTrue();
    expect(str_contains($output, 'EN</a> | <span>PT</span>'))->toBeTrue();
});

it('renders language selector without div and en-us', function () {
    $this->l10n->setLang('en-us');
    $_GET['action'] = 'ledger_entries';
    ob_start();
    Html::languageSelector($this->l10n, false);
    $output = ob_get_clean();
    expect(str_contains($output, '<span>EN</span> | <a href='))->toBeTrue();
    expect(str_contains($output, '<div>'))->toBeFalse();
});
