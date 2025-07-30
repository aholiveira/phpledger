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
    ?>
    <!DOCTYPE html>
    <html lang="pt-PT">
    <?php include "header.php"; ?>
    <meta http-equiv="REFRESH" content="5; URL=index.php">

    <body>
        <p>Your session has expired! Please login again.</p>
    </body>

    </html>
    <?php
    exit(1);
} else {
    $_SESSION['expires'] = time() + 3600;
    session_write_close();
    $object_factory = new object_factory();
    $view_factory = new view_factory();
}
