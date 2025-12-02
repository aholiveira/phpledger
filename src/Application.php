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
    public static function init(): void
    {
        self::defineGitHash();
        self::sendHeaders();
        self::bootstrap();
        self::guardSession();
        if (ObjectFactory::dataStorage()->check() === false) {
            if (basename($_SERVER['SCRIPT_NAME']) !== 'update.php') {
                Redirector::to("update.php");
            }
        }
        self::applyTimezone();
        self::updateUserLastVisited();
    }
    private static function defineGitHash(): void
    {
        if (!defined('GITHASH')) {
            $gitHead = ROOT_DIR . "/.git/ORIG_HEAD";
            $hash = file_exists($gitHead) ? substr(file_get_contents($gitHead), 0, 12) : "main";
            define("GITHASH", $hash);
        }
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
        SessionManager::start();
        L10n::init();
        ConfigPath::ensureMigrated();
        Config::init(ConfigPath::get());
        Logger::init(Path::combine(ROOT_DIR, "logs", "ledger.log"), LogLevel::INFO);
        $backend = Config::get("storage.type") ??  "mysql";
        ObjectFactory::init($backend);
    }
    private static function guardSession(): void
    {
        Logger::instance()->debug("Guarding session in Application::guardSession");
        $publicPages = ['index.php', 'reset_password.php', 'update.php'];
        SessionManager::guard($publicPages, SESSION_EXPIRE);
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
            $excluded = ['index.php', 'update.php', 'reset_password.php', 'forgot_password.php'];
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
