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

$config = new config();
include ROOT_DIR . "/config.php";
$pagetitle = "Redefini&ccedil;o de palavra-passe";

?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
    <?php
    $object_factory = new object_factory($config);
    $user = $object_factory->user();
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (array_key_exists("token_id", $_GET)) {
            if (is_null($user->getByToken($_GET["token_id"]))) {
                print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                print "<p>Token invalido<br></p>";
                print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                exit(1);
            } else {
                if (!$user->isTokenValid($_GET["token_id"])) {
                    print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                    print "<p>Token invalido ou expirado<br></p>";
                    print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                    exit(1);
                }
            }
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (array_key_exists("password", $_POST) && array_key_exists("verify_password", $_POST)) {
            if ($_POST["password"] == $_POST["verify_password"]) {
                if (!is_null($user->getByToken($_POST["token_id"])) && $user->isTokenValid($_POST["token_id"])) {
                    $user->setPassword($_POST["password"]);
                    $user->setToken('');
                    $user->setTokenExpiry(null);
                    if ($user->save()) {
                        print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                        print "<p>Palavra-passe alterada com sucesso<br></p>";
                        print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                        exit(1);
                    } else {
                        print "<p>Erro ao alterar utilizador<br></p>";
                        print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                        exit(1);
                    }
                }
            } else {
                print "<meta http-equiv='REFRESH' content='10; URL=reset_password.php?token_id={$_POST['token_id']}'>";
                print "<p>Palavras-passe introduzidas nao sao iguais<br></p>";
                exit(1);
            }
        } else {
            print "<meta http-equiv='REFRESH' content='10; URL=reset_password.php?token_id={$_POST['token_id']}'>";
            print "<p>Tem que indicar uma palavra-passe<br></p>";
            exit(1);
        }
    }
    ?>
</head>

<body onload="javascript:document.login.password.focus();">
    <?php
    ?>
    <div id="login">
        <h1>Gest&atilde;o financeira</h1>
        <p>Redefini&ccedil;&atilde;o de palavra-passe</p>
        <form method="POST" action="reset_password.php" name="reset_password">
            <input type="hidden" name="token_id" value="<?php print $_GET["token_id"]; ?>" />
            <table>
                <tr>
                    <td>Nova palavra-passe: </td>
                    <td><input size="10" maxlength="250" type="password" name="password" value="" required /></td>
                </tr>
                <tr>
                    <td>Confirmar palavra-passe: </td>
                    <td><input size="10" maxlength="250" type="password" name="verify_password" value="" required /></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Repor" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>