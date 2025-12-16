<?php

use PHPLedger\Controllers\AbstractViewController;
use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\RequestInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Util\UiBuilder;

uses()->group('abstract-view');

class DummyViewController extends AbstractViewController
{
    protected function handle(): void {}
    public function getUiData(): array
    {
        return $this->uiData;
    }
    public function publicBuildLanguageSelectorHtml(string $current): string
    {
        return $this->buildLanguageSelectorHtml($current);
    }
    public function getBaseLabels(L10nServiceInterface $l10n): array
    {
        return $this->buildL10nLabels($l10n, ['yes', 'no', 'add']);
    }
};

beforeEach(function () {
    $this->request = Mockery::mock(RequestInterface::class);
    $this->l10n = Mockery::mock(L10nServiceInterface::class);
    $this->session = Mockery::mock(SessionServiceInterface::class);
    $this->csrf = Mockery::mock(\PHPLedger\Contracts\CsrfServiceInterface::class);
    $this->app = Mockery::mock(ApplicationObjectInterface::class);

    $this->app->shouldReceive('l10n')->andReturn($this->l10n);
    $this->app->shouldReceive('session')->andReturn($this->session);
    $this->app->shouldReceive('csrf')->andReturn($this->csrf);

    $this->csrf->shouldReceive('inputField')->andReturn('<input type="hidden" name="_csrf_token" value="token">');

    $this->l10n->shouldReceive('lang')->andReturn('pt-pt');
    $this->l10n->shouldReceive('l')->andReturnUsing(fn($key, $param = null) => $param ? "$key:$param" : $key);

    $this->session->shouldReceive('get')->with('expires', Mockery::any())->andReturn(time());
    $this->session->shouldReceive('get')->with('isAdmin', false)->andReturn(true);

    $this->request->shouldReceive('all')->andReturn(['action' => 'accounts', 'foo' => 'bar']);
    $this->request->shouldReceive('input')->with('action')->andReturn('accounts');
});


it('initializes uiData correctly', function () {
    $controller = new DummyViewController();
    $controller->handleRequest($this->app, $this->request);
    $ui = $controller->getUiData();

    expect($ui)->toHaveKey('label')
        ->and($ui['label'])->toHaveKey('accounts')
        ->and($ui)->toHaveKey('menu')
        ->and($ui['menu'])->toHaveKey('accounts')
        ->and($ui)->toHaveKey('footer')
        ->and($ui['footer'])->toHaveKeys(['repo', 'versionText', 'sessionExpires', 'languageSelectorHtml'])
        ->and($ui)->toHaveKey('ui')
        ->and($ui['ui'])->toBeInstanceOf(UiBuilder::class)
        ->and($ui)->toHaveKey('isAdmin')
        ->and($ui['isAdmin'])->toBeTrue()
        ->and($ui)->toHaveKey('lang')
        ->and($ui['lang'])->toBe('pt-pt')
        ->and($ui)->toHaveKey('csrf');
});

it('builds base labels correctly', function () {
    $controller = new DummyViewController();
    $labels = $controller->getBaseLabels($this->l10n);

    expect($labels)->toBe([
        'yes' => 'yes',
        'no' => 'no',
        'add' => 'add',
    ]);
});

it('builds language selector html with request parameters', function () {
    $controller = new DummyViewController();

    $controller->handleRequest($this->app, $this->request);
    $html = $controller->getUiData()['footer']['languageSelectorHtml'];

    expect($html)->toContain('foo=bar')
        ->and($html)->toContain('lang=en-us')
        ->and($html)->toContain('<a href=');
});

it('toggles language correctly', function () {
    $controller = new DummyViewController();
    $this->l10n->shouldReceive('lang')->andReturn('en-us');

    $controller->handleRequest($this->app, $this->request);
    $html = $controller->publicBuildLanguageSelectorHtml('en-us');

    expect($html)->toContain('lang=pt-pt')
        ->and($html)->toContain('<a href=');
});
