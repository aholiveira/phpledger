<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

if (!defined("ROOT_DIR")) {
    include_once __DIR__ . "/prepend.php";
}
use \PHPLedger\Util\Config;
use \PHPLedger\Util\Html;
use \PHPLedger\Util\SessionManager;

config::init(__DIR__ . '/config.json');
if (isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
    SessionManager::logout();
}
if (isset($_SESSION['user']) && basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
    $secure = !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1);
    setcookie('current_url', rawurlencode($_SERVER['REQUEST_URI']), [
        'expires' => time() + 3600,
        'path' => dirname($_SERVER['SCRIPT_NAME']) . '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}
if (!isset($_SESSION['user']) && basename($_SERVER['SCRIPT_NAME']) !== 'reset_password.php') {
    if (!headers_sent()) {
        header("Location: index.php");
        exit();
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="pt-PT">
        <?php Html::header($pagetitle); ?>
        <meta http-equiv="refresh" content="5;url=index.php">
        <script>
            window.addEventListener("DOMContentLoaded", () => {
                myalert("Your session has expired! Please login again.", () => {
                    window.location.href = "index.php";
                });
            });
        </script>
        </head>

        <body> </body>

        </html>
    <?php }
    exit();
} else {
    $_SESSION['expires'] = time() + 3600;
    if (!isset($_SESSION['timezone']) && isset($_COOKIE['timezone']) && in_array($_COOKIE['timezone'], timezone_identifiers_list())) {
        $_SESSION['timezone'] = $_COOKIE['timezone'];
    }
    $tz = $_SESSION['timezone'] ?? config::get("timezone");
    date_default_timezone_set(in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC');
}
