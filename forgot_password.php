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
include __DIR__ . "/prepend.php";
$config = new config();
include ROOT_DIR . "/config.php";
$pagetitle = "Recupera&ccedil;&atilde;o de palavra-passe";
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
    <?php
    $object_factory = new object_factory($config);
    $user = $object_factory->user();
    $message = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (array_key_exists("username", $_POST) && array_key_exists("email", $_POST)) {
            $user->getByUsername($_POST["username"]);
            if ($user->getUsername() == $_POST["username"] && $user->getEmail() == $_POST["email"]) {
                if ($user->resetPassword()) {
                    //print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                    $message = "<p>Ir&aacute; receber um email com um link para efectuar a reposicao da palavra-passe.<br></p>";
                } else {
                    $message = "Falhou a criacao do token de reposicao. Contacte o administrador ou tente novamente.";
                }
            } else {
                $message = "Os dados indicados estao errados.";
            }
        } else {
            $message = "Indique o username e o email registados na aplicacao";
        }
    }
    ?>
</head>

<body onload="document.getElementById('username').focus();">
    <?php
    ?>
    <div id="login">
        <h1><?php print $config->getParameter("title"); ?></h1>
        <p>Reposi&ccedil;&atilde;o de palavra-passe</p>
        <form method="POST" action="forgot_password.php" name="forgot_password">
            <table>
                <tr>
                    <td>Utilizador: </td>
                    <td><input id="username" size="50" maxlength="250" type="text" name="username" value="" required></td>
                </tr>
                <tr>
                    <td>Endere&ccedil;o de email: </td>
                    <td><input size="50" maxlength="250" type="text" name="email" value="" required></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class='error'><?php print $message; ?></p>
                    </td>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Repor"></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>