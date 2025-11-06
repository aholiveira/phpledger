<?php

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

const BACKEND = "mysql";
const VERSION = "0.4.27";
const ROOT_DIR = __DIR__;
const OBJECTS_DIR = ROOT_DIR . "/objects";
const VIEWS_DIR = ROOT_DIR . "/views";
const UTILS_DIR = ROOT_DIR . "/util";

$gitHead = ROOT_DIR . "/.git/ORIG_HEAD";
define("GITHASH", file_exists($gitHead) ? substr(file_get_contents($gitHead), 0, 12) : "main");
if (defined("DEBUG") && DEBUG === 1) {
    openlog("contas-dev-php", LOG_PID, LOG_DAEMON);
    syslog(LOG_INFO, __FILE__);
    closelog();
}
@header('Cache-Control: no-cache');
@header('X-XSS-Protection: 1; mode=block');
@header('X-Frame-Options: DENY');
@header('X-Content-Type-Options: nosniff');
@header('Strict-Transport-Security: max-age=7776000');
@header('Referrer-Policy: strict-origin-when-cross-origin');
#@header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; style-src 'self' 'unsafe-inline'; script-src * ");

require_once OBJECTS_DIR . '/authentication.php';
require_once OBJECTS_DIR . '/config.class.php';
require_once OBJECTS_DIR . '/object_factory.php';
require_once OBJECTS_DIR . '/email.php';
require_once VIEWS_DIR . '/view_factory.php';
require_once UTILS_DIR . '/csrf.php';
require_once UTILS_DIR . '/logger.php';
require_once UTILS_DIR . '/dateparser.php';
require_once UTILS_DIR . '/ledgerentrycontroller.php';
require_once UTILS_DIR . '/redirector.php';
require_once UTILS_DIR . '/sessionmanager.php';
require_once UTILS_DIR . '/l10n.php';
require_once ROOT_DIR . '/html.php';

SessionManager::start();
l10n::init();

/**
 * Prints variable
 * @param mixed $var variable to print
 * @param string $comment comment to include before and after the variable printout
 * @param bool $debug
 *  * if false, the default, prints ALWAYS.
 *  * if true, print only if DEBUG is defined and true
 */
$logger = new Logger(ROOT_DIR . "/logs/ledger.log");
function debug_print($text)
{
    if (defined("DEBUG") && DEBUG === 1) {
        print (nl2br("####DEBUG#$text#DEBUG####<br>\n"));
    }
}
function normalize_number(?float $number): string
{
    return null === $number ? "" : number_format($number, 2);
}
