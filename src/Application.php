<?php

namespace PHPLedger;

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

class Application
{
    private static string $errorMessage = "";
    private static ?ObjectFactory $objectFactory = null;
    private static ?SessionManager $sessionManager = null;
    private static ?Logger $logger = null;
    private static ?Redirector $redirector = null;
    public static function init(): void
    {
        self::setDependencies();
        self::sendHeaders();
        self::bootstrap();
        self::guardSession();
        if (self::$objectFactory::dataStorage()->check() === false && ($_GET['action'] ?? '') !== 'update') {
            self::$redirector::to("index.php?action=update");
        }
        self::applyTimezone();
        self::updateUserLastVisited();
    }

    public static function setDependencies(
        ?ObjectFactory $objectFactory = null,
        ?SessionManager $sessionManager = null,
        ?Logger $logger = null,
        ?Redirector $redirector = null
    ): void {
        self::$objectFactory = $objectFactory;
        self::$sessionManager = $sessionManager;
        self::$logger = $logger;
        self::$redirector = $redirector;
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
    private static function bootstrap(): void
    {
        self::$sessionManager::start();
        L10n::init();
        ConfigPath::ensureMigrated();
        Config::init(ConfigPath::get());
        self::$logger::init(Path::combine(ROOT_DIR, "logs", "ledger.log"), LogLevel::INFO);
        $backend = Config::get("storage.type") ??  "mysql";
        self::$objectFactory::init($backend);
    }
    private static function guardSession(): void
    {
        self::$logger::instance()->debug("Guarding session in Application::guardSession");
        $publicPages = ['index.php'];
        self::$sessionManager::guard($publicPages, SESSION_EXPIRE);
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
        self::$logger::instance()->debug("Applying timezone: " . ($tz ?? 'UTC'));
        date_default_timezone_set(
            in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC'
        );
    }
    private static function updateUserLastVisited(): void
    {
        self::$logger::instance()->debug("Updating user's last visited page");
        if (!empty($_SESSION['user'])) {
            // Exclude certain pages from being recorded as "lastVisited" to avoid redirect loops
            $page = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? ''));
            $excluded = ['index.php'];
            if (in_array($page, $excluded, true)) {
                return;
            }
            $factory = self::$objectFactory::defaults();
            $defaults = $factory::getByUsername($_SESSION['user']) ?? $factory::init();
            $defaults->lastVisitedUri = $_SERVER['REQUEST_URI'] ?? '/';
            $defaults->lastVisitedAt = time();
            $defaults->update();
        }
    }
}
