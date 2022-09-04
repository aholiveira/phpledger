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
    'lifetime' => 3600,
    'path' => substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1),
    'samesite' => 'Strict; '
);
session_set_cookie_params($cookie_params);
session_start();
if (array_key_exists("do_logout", $_GET) && $_GET["do_logout"] == 1) {
    session_destroy();
}
/**
 * User was supplied
 * Do user authentication
 */
if (array_key_exists("userid", $_POST)) {
    $config = new config();
    include ROOT_DIR . "/config.php";
    $host = $config->getParameter("host");
    $dbase = $config->getParameter("database");
    // if (!auth_on_config()) {
    /**
     * @todo Create a user interface to generate config.php file
     */
    //  print "Verifique o ficheiro config.php<br/>\r\n";
    //print "O ficheiro deve conter credenciais para acesso 'a base de dados.<br/>\r\n";
    //  print "Consulte o ficheiro config.php.sample para um exemplo da configuracao.<br/>\r\n";
    //  print "A aplicacao nao pode continuar sem um ficheiro correctamente configurado.<br/>\r\n";
    //  exit(0);
    //}
    /**
     * Bypass internal auth and do mysql auth
     * while we don't have a user interface to
     * generate 
     */
    do_mysql_authentication();
    $object_factory = new object_factory($config);
    $userauth = do_authentication();
    if ($userauth) {
        $_SESSION['user'] = $_POST["userid"];
        $data_storage = $object_factory->data_storage();
        if ($data_storage->check()) {
            $defaults = $object_factory->defaults();
            $defaults->getById(1);
            $defaults->entry_date = date("Y-m-d");
            $defaults->save();
            header("Location: ledger_entries.php");
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
    return ($db_link instanceof mysqli);
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
        $user_object->setId($user_object->getFreeId());
        $user_object->setUsername($_POST["userid"]);
        $user_object->setPassword($_POST["pass"]);
        $user_object->setFullname('');
        $user_object->setRole(1);
        $user_object->setActive(1);
        $user_object->setEmail('');
        $user_object->save();
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
    global $config;
    $retval = false;
    /**
     * Do we have DB credentials on config file?
     * Choose user authentication method
     */
    $retval = do_mysql_authentication();
    $object_factory = new object_factory($config);
    $data_storage = $object_factory->data_storage();
    if (!$data_storage->check()) {
        if (!headers_sent()) {
            header('Location: update.php');
        } else {
            print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
        }
    }
    if (auth_on_config()) {
        try {
            $retval = do_internal_authentication();
        } catch (Exception $ex) {
            $retval = do_mysql_authentication();
        }
    } else {
        $retval = do_mysql_authentication();
    }
    return $retval;
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
</head>

<body onload="javascript:document.getElementById('userid').focus();">
    <div id="login">
        <h1>Gest&atilde;o financeira</h1>
        <p>Introduza o seu nome de utilizador e password para entrar na aplica&ccedil;&atilde;o.</p>
        <?php
        if (array_key_exists("userid", $_POST) && !$userauth) {
            print "<p style=\"color: #cc0000\">Utilizador e/ou password inv&aacute;lidos</p>\n";
        }
        ?>
        <form method="POST" action="index.php" name="login">
            <table>
                <tr>
                    <td>Utilizador: </td>
                    <td><input size="10" maxlength="10" type="text" name="userid" id="userid" value="<?php (array_key_exists("userid", $_POST) ? $_POST["userid"] : "") ?>" /></td>
                </tr>
                <tr>
                    <td>Password: </td>
                    <td><input size="10" maxlength="10" type="password" name="pass" value="" /></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Entrar" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>