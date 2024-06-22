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
$cookie_params = array(
    'lifetime' => 0,
    'path' => substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),
    'samesite' => 'Strict',
    'secure' => true,
    'httponly' => true
);
session_set_cookie_params($cookie_params);
session_start();
config::init(__DIR__ . '/config.json');

if (isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
    $_SESSION = array();
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
    $host = config::get("host");
    $dbase = config::get("database");
    if (strlen(config::get("user")) > 0 && strlen(config::get("password")) > 0) {
        $user = config::get("user");
        $pass = config::get("password");
    } else {
        $user = $_SESSION['user'];
        $pass = $_SESSION['pass'];
        config::set("user", $user);
        config::set("password", $pass);
    }
    session_write_close();
    try {
        $db_link = @new \mysqli($host, $user, $pass, $dbase);
        if ($db_link->connect_errno) {
            throw new Exception($db_link->connect_error);
        }
    } catch (\Exception $ex) {
        print "<p>There was an Error [" . htmlentities($ex->getMessage()) . "] while connecting to the database.</p>";
        exit($db_link->connect_errno);
    }
    $object_factory = new object_factory();
    $view_factory = new view_factory();
}
