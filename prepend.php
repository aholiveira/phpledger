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
if (file_exists(realpath(constant("ROOT_DIR") . "/.git/ORIG_HEAD"))) {
    define("GITHASH", file_get_contents($filename = realpath(constant("ROOT_DIR") . "/.git/ORIG_HEAD"), false, null, 0, $length = 12));
} else {
    define("GITHASH", "main");
}
#exec("git show -s --format=%ci", $gitresult);
#define("GITDATE", $gitresult[0]);
if (defined("DEBUG") && constant("DEBUG") == 1) {
    openlog("contas-dev-php", LOG_PID, LOG_DAEMON);
    syslog(LOG_INFO, __FILE__);
    closelog();
}
if (session_status() == PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", true);
    ini_set("session.sid_bits_per_character", 5);
    ini_set("session.sid_length", 64);
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
include constant("OBJECTS_DIR") . '/authentication.php';
include constant("OBJECTS_DIR") . '/config.class.php';
include constant("OBJECTS_DIR") . '/object_factory.php';
include constant("OBJECTS_DIR") . '/email.php';
include constant("VIEWS_DIR") . '/view_factory.php';
include constant("ROOT_DIR") . '/html.php';
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
    if (($debug && defined("DEBUG") && constant("DEBUG") == 1) || !$debug) {
        print "\r\n<pre>START###{$comment}###START<br>\r\n";
        print nl2br(print_r($var, true));
        print "\r\n<br>END###{$comment}###END</pre><br>\r\n";
    }
}
function debug_print($text)
{
    if (defined("DEBUG") && constant("DEBUG") == 1) {
        print(nl2br("####DEBUG#$text#DEBUG####<br>\n"));
    }
}
function normalize_number(?float $number): string
{
    if (is_null($number)) return "";
    return number_format($number, 2);
}
