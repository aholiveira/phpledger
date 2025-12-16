<?php

namespace PHPLedger;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Contracts\CsrfServiceInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\HeaderSenderInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\LoggerServiceInterface;
use PHPLedger\Contracts\LogLevel;
use PHPLedger\Contracts\RedirectorServiceInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Contracts\TimezoneServiceInterface;
use PHPLedger\Services\Config;
use PHPLedger\Services\CSRF;
use PHPLedger\Services\HeaderSender;
use PHPLedger\Services\L10n;
use PHPLedger\Services\Logger;
use PHPLedger\Services\Redirector;
use PHPLedger\Services\SessionManager;
use PHPLedger\Services\TimezoneService;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Storage\ReportFactory;
use PHPLedger\Util\ConfigPath;
use PHPLedger\Util\Path;

/**
 * @SuppressWarnings("php:S1448")
 */
final class Application implements ApplicationObjectInterface
{
    private string $errorMessage = "";
    private string $logfile;
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

    public static function create(): self
    {
        $app = new Application(empty($logfile) ?  Path::combine(ROOT_DIR, "logs", "ledger.log") : $logfile);
        $config = new Config();
        Config::init(ConfigPath::get());
        $backend = $config->get('storage.type', 'mysql');
        $app->setConfig($config);
        $app->setDataFactory(new ObjectFactory($backend));
        $app->setReportFactory(new ReportFactory($backend));
        $app->setSessionManager(new SessionManager());
        $app->setLogger(new Logger(empty($logfile) ? Path::combine(ROOT_DIR, "logs", "ledger.log") : $logfile));
        $app->setRedirector(new Redirector());
        $app->setL10n(new L10n());
        $app->setHeaderSender(new HeaderSender());
        $app->setTimezoneService(new TimezoneService());
        $app->setCsrf(new CSRF());
        return $app;
    }
    public function __construct(string $logfile = "")
    {
        $this->logfile = empty($logfile) ? Path::combine(ROOT_DIR, "logs", "ledger.log") : $logfile;
    }
    public function setDataFactory(DataObjectFactoryInterface $dataFactory): void
    {
        $this->dataFactory = $dataFactory;
    }
    public function setReportFactory(ReportFactory $reportFactory): void
    {
        $this->reportFactory = $reportFactory;
    }
    public function setSessionManager(SessionServiceInterface $session): void
    {
        $this->session = $session;
    }
    public function setLogger(LoggerServiceInterface $logger): void
    {
        $this->logger = $logger;
    }
    public function setRedirector(RedirectorServiceInterface $redirector): void
    {
        $this->redirector = $redirector;
    }
    public function setL10n(L10nServiceInterface $l10n): void
    {
        $this->l10n = $l10n;
    }
    public function setConfig(ConfigurationServiceInterface $config): void
    {
        $this->config = $config;
    }
    public function setTimezoneService(TimezoneServiceInterface $timezoneService): void
    {
        $this->timezoneService = $timezoneService;
    }
    public function setCsrf(CsrfServiceInterface $csrf): void
    {
        $this->csrf = $csrf;
    }
    public function csrf(): CsrfServiceInterface
    {
        return $this->csrf;
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
    public function dataFactory(): DataObjectFactoryInterface
    {
        $backend = $this->config()->get("storage.type") ?? "mysql";
        return $this->dataFactory ??= new ObjectFactory($backend);
    }
    public function reportFactory(): ReportFactory
    {
        $backend = $this->config()->get("storage.type") ?? "mysql";
        return $this->reportFactory ??= new ReportFactory($backend);
    }
    public function config(): ConfigurationServiceInterface
    {
        return $this->config ?? new Config(ConfigPath::get());
    }
    public function session(): SessionServiceInterface
    {
        return $this->session ??= new SessionManager();
    }
    public function l10n(): L10nServiceInterface
    {
        return $this->l10n ??= new L10n();
    }
    public function redirector(): RedirectorServiceInterface
    {
        return $this->redirector ??= new Redirector();
    }
    public function logger(): LoggerServiceInterface
    {
        return $this->logger ??= new Logger($this->logfile, LogLevel::INFO);
    }
    public function setHeaderSender(HeaderSenderInterface $headerSender): void
    {
        $this->headerSender = $headerSender;
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
        if (!isset($this->headerSender)) {
            $this->headerSender = new HeaderSender();
        }
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
        Config::init(ConfigPath::get());
    }
    private function applyTimezone(): void
    {
        if (!isset($this->timezoneService)) {
            $this->timezoneService = new TimezoneService();
        }
        $tz = $this->timezoneService->apply($this->config()->get("timezone", ''));
        $this->logger()->debug("Applied timezone: $tz");
    }
}
