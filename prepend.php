<?php

use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
/**
 * Prepended file on each call to a PHP file
 * This does basic defines and checks if PHP version is supported
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 * @since 0.2.0
 *
 */

if (PHP_VERSION_ID < 70000) {
    die('PHP >= 7.0.0 required');
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Logger;
use PHPLedger\Util\SessionManager;

const BACKEND = "mysql";
const VERSION = "0.4.400";
const ROOT_DIR = __DIR__;
const SESSION_EXPIRE = 3600;

$logger = new Logger(ROOT_DIR . "/logs/ledger.log");

$gitHead = ROOT_DIR . "/.git/ORIG_HEAD";
define("GITHASH", file_exists($gitHead) ? substr(file_get_contents($gitHead), 0, 12) : "main");
@header('Cache-Control: no-cache');
@header('X-XSS-Protection: 1; mode=block');
@header('X-Frame-Options: DENY');
@header('X-Content-Type-Options: nosniff');
@header('Strict-Transport-Security: max-age=7776000');
@header('Referrer-Policy: strict-origin-when-cross-origin');
#@header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; style-src 'self' 'unsafe-inline'; script-src * ");

$PUBLIC_PAGES = ['index.php', 'reset_password.php', 'update.php'];
SessionManager::start();
L10n::init();
Config::init(ROOT_DIR . '/config.json');
# Identify the current PHP script
$currentPage = strtolower(basename($_SERVER['SCRIPT_NAME']));
$isPublic = in_array($currentPage, $PUBLIC_PAGES, true);

# --- SESSION EXPIRED? ---
if (SessionManager::isExpired()) {
    SessionManager::logout();
    SessionManager::start();
    if (!$isPublic && !headers_sent()) {
        Redirector::to("Location: index.php?expired=1&lang=" . L10n::$lang);
    }
}

# --- AUTH REQUIRED FOR PROTECTED PAGES ---
if (!$isPublic && !isset($_SESSION['user'])) {
    if (!headers_sent()) {
        Redirector::to("index.php");
    }
}
# --- SESSION STILL VALID OR PUBLIC PAGE ---
$_SESSION['expires'] = time() + SESSION_EXPIRE;
# Timezone load (unchanged)
if (
    !isset($_SESSION['timezone'])
    && isset($_COOKIE['timezone'])
    && in_array($_COOKIE['timezone'], timezone_identifiers_list(), true)
) {
    $_SESSION['timezone'] = $_COOKIE['timezone'];
}

$tz = $_SESSION['timezone'] ?? Config::get("timezone");
date_default_timezone_set(in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC');
ObjectFactory::init("mysql", $logger);
if (!empty($_SESSION['user'])) {
    $defaults = ObjectFactory::defaults()::getByUsername($_SESSION['user']);
    $defaults->lastVisited = $_SERVER['REQUEST_URI'];
    $defaults->update();
}
function debugPrint($text)
{
    if (defined("DEBUG") && DEBUG === 1) {
        print nl2br("####DEBUG#$text#DEBUG####<br>\n");
    }
}
function normalizeNumber(?float $number): string
{
    return null === $number ? "" : number_format($number, 2);
}
