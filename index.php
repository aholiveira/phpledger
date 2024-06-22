<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
global $user;
global $pass;
$cookie_params = array(
    'lifetime' => 0,
    'path' => substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),
    'samesite' => 'Strict',
    'secure' => true,
    'httponly' => true
);
session_set_cookie_params($cookie_params);
session_start();
if (array_key_exists("do_logout", $_GET) && $_GET["do_logout"] == 1) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
} else {
    $_SESSION['expires'] = time() + 3600;
}
if (!defined("ROOT_DIR")) {
    include "prepend.php";
}
config::init(__DIR__ . '/config.json');

$userauth = false;
$input_variables_filter = array(
    'username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'password' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
);

$post_user = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    $post_user = $filtered_input["username"];
    $post_pass = empty($filtered_input["password"]) ? "" : $filtered_input["password"];
}
$object_factory = new object_factory();
$data_storage = $object_factory->data_storage();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!$data_storage->check()) {
        if (!headers_sent()) {
            header('Location: update.php');
        } else {
            print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
        }
        exit(0);
    }
}
if (!empty($filtered_input["username"])) {
    $userauth = authentication::authenticate($filtered_input["username"], $filtered_input["password"]);
    if ($userauth) {
        $_SESSION['user'] = $filtered_input["username"];
        session_write_close();
        if ($data_storage->check()) {
            $defaults = $object_factory->defaults();
            $defaults = $defaults->getById(1);
            $defaults->entry_date = date("Y-m-d");
            $defaults->update();
            header("Location: ledger_entries.php?filter_sdate=" . date('Y-m-01'));
        } else {
            if (!headers_sent()) {
                header('Location: update.php');
            } else {
                print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body onload="javascript:document.getElementById('username').focus();">
    <div id="login">
        <h1><?php print config::get("title"); ?></h1>
        <p>Introduza o seu nome de utilizador e password para entrar na aplica&ccedil;&atilde;o.</p>
        <?php
        if (array_key_exists("username", $_POST) && !$userauth) {
            print "<p style=\"color: #cc0000\">Utilizador e/ou password inv&aacute;lidos</p>\n";
        }
        ?>
        <form method="POST" action="index.php" name="login">
            <table>
                <tr>
                    <td>Utilizador:</td>
                    <td><input size="10" maxlength="50" type="text" name="username" id="username" autocomplete="off" value="<?php print $post_user; ?>"></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input size="10" maxlength="255" type="password" name="password" value=""></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Entrar"></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>