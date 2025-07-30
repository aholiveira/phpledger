<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

if (!defined("ROOT_DIR")) {
    include __DIR__ . "/prepend.php";
}
config::init(__DIR__ . '/config.json');
if (isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
}
if (!isset($_SESSION['user'])) {
    if (!headers_sent()) {
        header("Location: index.php");
        exit;
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="pt-PT">
        <?php include "header.php"; ?>
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
    exit(1);
} else {
    $_SESSION['expires'] = time() + 3600;
    if (!isset($_SESSION['timezone']) && isset($_COOKIE['timezone']) && in_array($_COOKIE['timezone'], timezone_identifiers_list())) {
        $_SESSION['timezone'] = $_COOKIE['timezone'];
    }
    $tz = $_SESSION['timezone'] ?? config::get("timezone");
    date_default_timezone_set(in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC');
    session_write_close();
    $object_factory = new object_factory();
    $view_factory = new view_factory();
}
