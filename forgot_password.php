<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
require_once __DIR__ . "/prepend.php";
use \PHPLedger\Storage\ObjectFactory;
use \PHPLedger\Util\Config;
use \PHPLedger\Util\Html;
use \PHPLedger\Util\L10n;

Config::init(__DIR__ . '/config.json');
$pagetitle = "Recupera&ccedil;&atilde;o de palavra-passe";
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header($pagetitle); ?>
    <?php
    $user = ObjectFactory::user();
    $message = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (array_key_exists("username", $_POST) && array_key_exists("email", $_POST)) {
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_ENCODED);
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_ENCODED);
            $user->getByUsername($username);
            if (strcasecmp($user->getUsername(), $username) == 0 && strcasecmp(filter_var($user->getEmail(), FILTER_SANITIZE_ENCODED), $email) == 0) {
                $message = $user->resetPassword() ?
                    "<p>Ir&aacute; receber um email com um link para efectuar a reposicao da palavra-passe.<br></p>"
                    :
                    "Falhou a criacao do token de reposicao ou o envio do email. Verifique as configuracoes ou os dados fornecidos e tente novamente.";
            } else {
                $message = "Os dados indicados est&atilde;o errados.";
            }
        } else {
            $message = "Indique o username e o email registados na aplica&ccedil;&atilde;o";
        }
    }
    ?>
</head>

<body onload="document.getElementById('username').focus();">
    <?php
    ?>
    <div id="login">
        <h1><?php print config::get("title"); ?></h1>
        <p>Reposi&ccedil;&atilde;o de palavra-passe</p>
        <form method="POST" action="forgot_password.php" name="forgot_password">
            <table>
                <tr>
                    <td>Utilizador: </td>
                    <td><input id="username" size="50" maxlength="250" type="text" name="username" value="" required>
                    </td>
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
