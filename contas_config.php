<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

$cookie_params = array(
    'lifetime' => 3600,
    'path' => substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),
    'samesite' => 'Strict',
    'httponly' => false
);
//session_cache_limiter('nocache');
session_set_cookie_params($cookie_params);
session_start();
$config = new config();
include __DIR__ . '/config.php';
if (!isset($_SESSION['user'])) {
?>
    <!DOCTYPE html>
    <html>
    <?php include "header.php"; ?>
    <meta http-equiv="REFRESH" content="5; URL=index.php">

    <body>
        <p>Your session has expired! Please login again.</p>
    </body>

    </html>
<?php
    exit(1);
} else {
    $host = $config->getParameter("host");
    $dbase = $config->getParameter("database");
    if (strlen($config->getParameter("user")) > 0 && strlen($config->getParameter("password")) > 0) {
        $user = $config->getParameter("user");
        $pass = $config->getParameter("password");
    } else {
        $user = $_SESSION['user'];
        $pass = $_SESSION['pass'];
        $config->setParameterValue("user", $user);
        $config->setParameterValue("password", $pass);
    }
    $db_link = new mysqli($host, $user, $pass, $dbase) or die(mysqli_connect_error());
    $object_factory = new object_factory($config);
    $view_factory = new view_factory();
}
