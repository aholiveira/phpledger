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
if (version_compare(PHP_VERSION, '7.0.0') < 0) {
    die('PHP >= 7.0.0 required');
}
define("BACKEND", "mysql");
define("VERSION", "0.2.0");
define("ROOT_DIR", __DIR__);
define("OBJECTS_DIR", constant("ROOT_DIR") . "/objects");
define("VIEWS_DIR", constant("ROOT_DIR") . "/views");

$gitHead = ROOT_DIR . "/.git/ORIG_HEAD";
define("GITHASH", file_exists($gitHead) ? substr(file_get_contents($gitHead), 0, 12) : "main");
#exec("git show -s --format=%ci", $gitresult);
#define("GITDATE", $gitresult[0]);
if (defined("DEBUG") && constant("DEBUG") == 1) {
    openlog("contas-dev-php", LOG_PID, LOG_DAEMON);
    syslog(LOG_INFO, __FILE__);
    closelog();
}
$secure = !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1);
$cookie_params = [
    'lifetime' => 0,
    'path' => dirname($_SERVER['SCRIPT_NAME']) . '/',
    'samesite' => 'Strict',
    'secure' => $secure,
    'httponly' => true
];
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params($cookie_params);
    session_start();
}
if (!headers_sent()) {
    header("Cache-Control: no-cache");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Strict-Transport-Security: max-age=7776000");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    #header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; style-src 'self' 'unsafe-inline'; script-src * ");
}
require_once OBJECTS_DIR . '/authentication.php';
require_once OBJECTS_DIR . '/config.class.php';
require_once OBJECTS_DIR . '/object_factory.php';
require_once OBJECTS_DIR . '/email.php';
require_once VIEWS_DIR . '/view_factory.php';
require_once ROOT_DIR . '/html.php';
require_once ROOT_DIR . '/util/csrf.php';
/**
 * Prints variable
 * @param mixed $var variable to print
 * @param string $comment comment to include before and after the variable printout
 * @param bool $debug
 *  * if false, the default, prints ALWAYS.
 *  * if true, print only if DEBUG is defined and true
 */
function print_var($var, $comment = "", bool $debug = false)
{
    if (($debug && defined("DEBUG") && DEBUG === 1) || !$debug) {
        print "\r\n<pre>START###{$comment}###START<br>\r\n";
        print nl2br(print_r($var, true));
        print "\r\n<br>END###{$comment}###END</pre><br>\r\n";
    }
}
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
