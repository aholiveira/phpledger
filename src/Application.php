<?php

namespace PHPLedger;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Contracts\SessionServiceInterface;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\ConfigPath;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Logger;
use PHPLedger\Util\LogLevel;
use PHPLedger\Util\Path;
use PHPLedger\Util\Redirector;
use PHPLedger\Util\SessionManager;

const SESSION_EXPIRE = 3600;

final class Application implements ApplicationObjectInterface
{
    private static string $errorMessage = "";
    private DataObjectFactoryInterface $dataFactory;
    private SessionManager $session;
    private Logger $logger;
    private Redirector $redirector;
    private L10n $l10n;
    private ConfigurationServiceInterface $config;
    private string $logfile;
    public function __construct(string $logfile = "")
    {
        $this->logfile = empty($logfile) ? Path::combine(ROOT_DIR, "logs", "ledger.log") : $logfile;
        self::sendHeaders();
        self::bootstrap();
        if ($this->dataFactory()::dataStorage()->check() === false && ($_GET['action'] ?? '') !== 'update') {
            Redirector::to("index.php?action=update");
        }
        self::applyTimezone();
        self::updateUserLastVisited();
    }
    public function dataFactory(): DataObjectFactoryInterface
    {
        $backend = Config::get("storage.type") ??  "mysql";
        return ($this->dataFactory ??= new ObjectFactory($backend));
    }
    public function config(): ConfigurationServiceInterface
    {
        return self::$config;
    }
    public function session(): SessionServiceInterface
    {
        return ($this->session ??= new SessionManager($this));
    }
    public function l10n(): L10n
    {
        return ($this->l10n ??= new L10n());
    }
    public function redirector(): Redirector
    {
        return ($this->redirector ??= new Redirector());
    }
    public function logger(): Logger
    {
        return ($this->logger ??= new Logger($this->logfile, LogLevel::INFO));
    }
    public static function setErrorMessage(string $message): void
    {
        self::$errorMessage = $message;
    }
    public static function clearErrorMessage(): void
    {
        self::$errorMessage = "";
    }
    public static function getErrorMessage(): string
    {
        return self::$errorMessage;
    }
    private static function sendHeaders(): void
    {
        if (!headers_sent()) {
            header('Cache-Control: no-cache');
            header('X-XSS-Protection: 1; mode=block');
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('Strict-Transport-Security: max-age=7776000');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
    private function bootstrap(): void
    {
        $this->session()->start();
        L10n::init();
        ConfigPath::ensureMigrated();
        Config::init(ConfigPath::get());
    }
    private static function applyTimezone(): void
    {
        if (
            empty($_SESSION['timezone']) &&
            !empty($_COOKIE['timezone']) &&
            in_array($_COOKIE['timezone'], timezone_identifiers_list(), true)
        ) {
            $_SESSION['timezone'] = $_COOKIE['timezone'];
        }

        $tz = $_SESSION['timezone'] ?? Config::get("timezone");
        Logger::instance()->debug("Applying timezone: " . ($tz ?? 'UTC'));
        date_default_timezone_set(
            in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC'
        );
    }
    private static function updateUserLastVisited(): void
    {
        Logger::instance()->debug("Updating user's last visited page");
        if (!empty($_SESSION['user'])) {
            // Exclude certain pages from being recorded as "lastVisited" to avoid redirect loops
            $page = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? ''));
            $excluded = ['index.php'];
            if (in_array($page, $excluded, true)) {
                return;
            }
            $factory = ObjectFactory::defaults();
            $defaults = $factory::getByUsername($_SESSION['user']) ?? $factory::init();
            $defaults->lastVisitedUri = $_SERVER['REQUEST_URI'] ?? '/';
            $defaults->lastVisitedAt = time();
            $defaults->update();
        }
    }
}
