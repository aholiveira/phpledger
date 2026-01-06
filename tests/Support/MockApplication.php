<?php

namespace PHPLedgerTests\Support;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\CsrfServiceInterface;
use PHPLedger\Contracts\RedirectorServiceInterface;
use PHPLedger\Contracts\LoggerServiceInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\HeaderSenderInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Contracts\TimezoneServiceInterface;
use PHPLedger\Services\FileResponseSender;
use PHPLedger\Storage\ReportFactory;
use Mockery;

final class MockApplication implements ApplicationObjectInterface
{
    public mixed $csrf;
    public mixed $redirector;
    public mixed $logger;
    public mixed $session;
    public mixed $l10n;
    public mixed $dataFactory;
    public mixed $headerSender;
    public mixed $config;
    public mixed $timezone;
    public mixed $fileResponseSender;
    public mixed $reportFactory;
    private string $errorMessage = "";

    public function __construct()
    {
        $this->csrf = Mockery::mock(CsrfServiceInterface::class);
        $this->csrf->shouldReceive('inputField')->andReturn('<input type="hidden" name="_csrf_token" value="token">');
        $this->csrf->shouldReceive('validateToken')->andReturn(true);

        $this->redirector = Mockery::mock(RedirectorServiceInterface::class);
        $this->redirector->shouldReceive('to')->andReturnNull();

        $this->logger = Mockery::mock(LoggerServiceInterface::class);
        $this->logger->shouldReceive('*')->andReturnNull();

        $this->session = Mockery::mock(SessionServiceInterface::class);
        $this->session->shouldReceive('start')->andReturnNull();

        $this->l10n = Mockery::mock(L10nServiceInterface::class);
        $this->l10n->shouldReceive('l')->andReturnUsing(fn($key) => $key);
        $this->l10n->shouldReceive('html')->andReturn('en-us');
        $this->l10n->shouldReceive('lang')->andReturn('en-us');

        $this->dataFactory = Mockery::mock(DataObjectFactoryInterface::class);
        $this->dataFactory->shouldReceive('dataStorage')->andReturn(Mockery::mock()->shouldReceive('check')->andReturn(true)->getMock());
        $this->dataFactory->shouldReceive('*')->andReturn(Mockery::mock());

        $this->headerSender = Mockery::mock(HeaderSenderInterface::class);
        $this->headerSender->shouldReceive('sent')->andReturn(false);
        $this->headerSender->shouldReceive('send')->andReturnNull();

        $this->config = Mockery::mock(ConfigurationServiceInterface::class);
        $this->config->shouldReceive('get')->andReturn('');

        $this->timezone = Mockery::mock(TimezoneServiceInterface::class);
        $this->timezone->shouldReceive('apply')->andReturn('UTC');

        $this->fileResponseSender = new FileResponseSender($this->headerSender);
        $this->reportFactory = new ReportFactory("mysql");
    }

    public function config(): ConfigurationServiceInterface { return $this->config; }
    public function dataFactory(): DataObjectFactoryInterface { return $this->dataFactory; }
    public function reportFactory(): ReportFactory { return $this->reportFactory; }
    public function l10n(): L10nServiceInterface { return $this->l10n; }
    public function logger(): LoggerServiceInterface { return $this->logger; }
    public function session(): SessionServiceInterface { return $this->session; }
    public function redirector(): RedirectorServiceInterface { return $this->redirector; }
    public function csrf(): CsrfServiceInterface { return $this->csrf; }
    public function headerSender(): HeaderSenderInterface { return $this->headerSender; }
    public function fileResponseSender(): FileResponseSender { return $this->fileResponseSender; }
    public function setErrorMessage(string $message): void { $this->errorMessage = $message; }
    public function clearErrorMessage(): void { $this->errorMessage = ''; }
    public function getErrorMessage(): string { return $this->errorMessage; }
    public function isInstalled(): bool { return true; }
}
