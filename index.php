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
    'httponly' => false
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
$config = new config();
include __DIR__ . '/config.php';

$userauth = false;
$object_factory = new object_factory($config);
$data_storage = $object_factory->data_storage();
if ($_SERVER["REQUEST_METHOD"] == 'GET') {
    #$host = $config->getParameter("host");
    #$dbase = $config->getParameter("database");
    if (auth_on_config()) {
        if (!$data_storage->check()) {
            if (!headers_sent()) {
                header('Location: update.php');
            } else {
                print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
            }
            exit(0);
        }
    }
}
if (array_key_exists("userid", $_POST) && null != $_POST["userid"]) {
    if (!auth_on_config()) {
        print "AUTH NOT ON CONFIG";
        do_mysql_authentication();
    }
    $userauth = do_authentication();
    if ($userauth) {
        $_SESSION['user'] = $_POST["userid"];
        session_write_close();
        if ($data_storage->check()) {
            $defaults = $object_factory->defaults();
            $defaults->getById(1);
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

function auth_on_config(): bool
{
    global $config;
    return (strlen($config->getParameter("user")) && strlen($config->getParameter("password")));
}
function do_mysql_authentication()
{
    global $config;
    /**
     * Do MySQL auth
     */
    $username = $_POST["userid"];
    $pass = md5($_POST["pass"]);
    $_SESSION['user'] = $username;
    $_SESSION['pass'] = $pass;
    $config->setParameterValue("user", $username);
    $config->setParameterValue("password", $pass);
    $db_link = @mysqli_connect($config->getParameter("host"), $username, $pass, $config->getParameter("database"));
    return ($db_link instanceof \mysqli);
}
/**
 * Create user (user migration from DB to internal auth), 
 * but only if it already exists on MySQL auth and it succeeds
 */
function verify_and_migrate_user()
{
    global $host;
    global $dbase;
    global $user_object;

    $retval = false;
    if (mysqli_connect($host, $_POST["userid"], md5($_POST["pass"]), $dbase)) {
        $retval = true;
        $user_object->setId($user_object->getNextId());
        $user_object->setUsername($_POST["userid"]);
        $user_object->setPassword($_POST["pass"]);
        $user_object->setFullname('');
        $user_object->setRole(1);
        $user_object->setActive(1);
        $user_object->setEmail('');
        $user_object->update();
    }
    return $retval;
}
function do_internal_authentication()
{
    global $config;
    global $host;
    global $dbase;
    global $db_link;
    global $object_factory;
    global $user_object;

    $username = $config->getParameter("user");
    $pass = $config->getParameter("password");
    $retval = false;
    $db_link = @mysqli_connect($host, $username, $pass, $dbase);
    if (!($db_link instanceof mysqli)) {
        /**
         * Config file credentials are invalid
         */
        print "Verifique o ficheiro config.php";
        exit(0);
    }
    /**
     * Config file credentials are valid.
     */
    $user_object = $object_factory->user();
    $user_object->getByUsername($_POST["userid"]);
    if (strcasecmp($user_object->getUsername(), $_POST["userid"]) == 0) {
        /**
         * User exists in internal auth DB
         * Authenticate user using data in the form
         */
        $retval = $user_object->verifyPassword($_POST["pass"]);
    } else {
        /**
         * User does not exist in internal DB.
         */
        $retval = verify_and_migrate_user();
    }
    return $retval;
}
function do_authentication()
{
    global $data_storage;
    $retval = false;
    /**
     * Do we have DB credentials on config file?
     * Choose user authentication method
     */
    if (auth_on_config()) {
        try {
            $retval = do_internal_authentication();
        } catch (\Exception $ex) {
            $retval = do_mysql_authentication();
        } finally {
            if (!$data_storage->check()) {
                if (!headers_sent()) {
                    header('Location: update.php');
                } else {
                    print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
                }
            }
        }
    } else {
        $retval = do_mysql_authentication();
    }
    return $retval;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body onload="javascript:document.getElementById('userid').focus();">
    <div id="login">
        <h1><?php print $config->getParameter("title"); ?></h1>
        <p>Introduza o seu nome de utilizador e password para entrar na aplica&ccedil;&atilde;o.</p>
        <?php
        if (array_key_exists("userid", $_POST) && !$userauth) {
            print "<p style=\"color: #cc0000\">Utilizador e/ou password inv&aacute;lidos</p>\n";
        }
        ?>
        <form method="POST" action="index.php" name="login">
            <table>
                <tr>
                    <td>Utilizador:</td>
                    <td><input size="10" maxlength="10" type="text" name="userid" id="userid" value="<?php (array_key_exists("userid", $_POST) ? $_POST["userid"] : "") ?>"></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input size="10" maxlength="10" type="password" name="pass" value=""></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Entrar"></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>