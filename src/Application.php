<?php

namespace PHPLedger;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Contracts\CsrfServiceInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\HeaderSenderInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\LoggerServiceInterface;
use PHPLedger\Contracts\RedirectorServiceInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Contracts\TimezoneServiceInterface;
use PHPLedger\Services\FileResponseSender;
use PHPLedger\Storage\ReportFactory;

final class Application implements ApplicationObjectInterface
{
    private string $errorMessage = "";
    private bool $needsUpdate;
    private ConfigurationServiceInterface $config;
    private DataObjectFactoryInterface $dataFactory;
    private ReportFactory $reportFactory;
    private SessionServiceInterface $session;
    private LoggerServiceInterface $logger;
    private RedirectorServiceInterface $redirector;
    private L10nServiceInterface $l10n;
    private HeaderSenderInterface $headerSender;
    private TimezoneServiceInterface $timezoneService;
    private CsrfServiceInterface $csrf;
    private FileResponseSender $fileResponseSender;

    /**
     * Suppress message too many parameters. This is intended.
     * @SuppressWarnings("php:S107")
     */
    public function __construct(
        ConfigurationServiceInterface $config,
        DataObjectFactoryInterface $dataFactory,
        ReportFactory $reportFactory,
        SessionServiceInterface $session,
        LoggerServiceInterface $logger,
        RedirectorServiceInterface $redirector,
        L10nServiceInterface $l10n,
        HeaderSenderInterface $headerSender,
        TimezoneServiceInterface $timezoneService,
        CsrfServiceInterface $csrf,
        FileResponseSender $fileResponseSender
    ) {
        $this->config = $config;
        $this->dataFactory = $dataFactory;
        $this->reportFactory = $reportFactory;
        $this->session = $session;
        $this->logger = $logger;
        $this->redirector = $redirector;
        $this->l10n = $l10n;
        $this->headerSender = $headerSender;
        $this->timezoneService = $timezoneService;
        $this->csrf = $csrf;
        $this->fileResponseSender = $fileResponseSender;
        $this->needsUpdate = false;
    }
    public function dataFactory(): DataObjectFactoryInterface
    {
        return $this->dataFactory;
    }
    public function reportFactory(): ReportFactory
    {
        return $this->reportFactory;
    }
    public function session(): SessionServiceInterface
    {
        return $this->session;
    }
    public function logger(): LoggerServiceInterface
    {
        return $this->logger;
    }
    public function redirector(): RedirectorServiceInterface
    {
        return $this->redirector;
    }
    public function l10n(): L10nServiceInterface
    {
        return $this->l10n;
    }
    public function config(): ConfigurationServiceInterface
    {
        return $this->config;
    }
    public function csrf(): CsrfServiceInterface
    {
        return $this->csrf;
    }
    public function headerSender(): HeaderSenderInterface
    {
        return $this->headerSender;
    }
    public function fileResponseSender(): FileResponseSender
    {
        return $this->fileResponseSender;
    }
    public function init(): void
    {
        $this->sendHeaders();
        $this->bootstrap();
        $this->needsUpdate = !($this->dataFactory()->dataStorage()->check());
        $this->applyTimezone();
    }
    public function needsUpdate(): bool
    {
        return $this->needsUpdate;
    }
    public function setErrorMessage(string $message): void
    {
        $this->errorMessage = $message;
    }
    public function clearErrorMessage(): void
    {
        $this->errorMessage = "";
    }
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
    private function sendHeaders(): void
    {
        if (!$this->headerSender->sent()) {
            $this->headerSender->send('Cache-Control: no-cache');
            $this->headerSender->send('X-XSS-Protection: 1; mode=block');
            $this->headerSender->send('X-Frame-Options: DENY');
            $this->headerSender->send('X-Content-Type-Options: nosniff');
            $this->headerSender->send('Strict-Transport-Security: max-age=7776000');
            $this->headerSender->send('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
    private function bootstrap(): void
    {
        $this->session()->start();
    }
    private function applyTimezone(): void
    {
        $tz = $this->timezoneService->apply($this->config()->get("timezone", ''));
        $this->logger()->debug("Applied timezone: $tz");
    }
}
