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

include __DIR__ . "/contas_config.php";
$pagetitle = "Redefini&ccedil;o de palavra-passe";

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
    <?php
    $object_factory = new object_factory();
    $user = $object_factory->user();

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $token_id = filter_input(INPUT_GET, "token_id", FILTER_SANITIZE_ENCODED);
        if (!empty($token_id)) {
            $user = $user::getByToken($token_id);
            if ($user instanceof user) {
                print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                print "<p>Token invalido<br></p>";
                print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                exit(1);
            } else {
                if (null !== $user && !$user->isTokenValid($token_id)) {
                    print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                    print "<p>Token invalido ou expirado<br></p>";
                    print "<p>Ir&aacute; ser redireccionado para a pagina inicial.<br></p>";
                    exit(1);
                }
            }
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $token_id = filter_input(INPUT_POST, "token_id", FILTER_SANITIZE_ENCODED);
        $password = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
        $verify_password = filter_input(INPUT_POST, "verify_password", FILTER_SANITIZE_ENCODED);
        if (array_key_exists("password", $_POST) && array_key_exists("verify_password", $_POST)) {
            if ($password == $verify_password) {
                $user = $user::getByToken($token_id);
                if (($user instanceof user) && $user->isTokenValid($token_id)) {
                    $user->setPassword($password);
                    $user->setToken('');
                    $user->setTokenExpiry(null);
                    if ($user->update()) {
                        print "<meta http-equiv='REFRESH' content='10; URL=index.php'>";
                        print "<p>Palavra-passe alterada com sucesso<br></p>";
                        print "<p>Ir&aacute; ser redireccionado para a p&aacute;gina inicial.<br></p>";
                        exit(1);
                    } else {
                        print "<p>Erro ao alterar utilizador<br></p>";
                        print "<p>Ir&aacute; ser redireccionado para a p&aacute;gina inicial.<br></p>";
                        exit(1);
                    }
                }
            } else {
                print "<meta http-equiv='REFRESH' content='10; URL=reset_password.php?token_id={$token_id}'>";
                print "<p>As palavras-passe introduzidas n&atilde;o s&atilde;o iguais<br></p>";
                exit(1);
            }
        } else {
            print "<meta http-equiv='REFRESH' content='10; URL=reset_password.php?token_id={$token_id}'>";
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
        <h1><?php print config::get("title"); ?></h1>
        <p>Redefini&ccedil;&atilde;o de palavra-passe</p>
        <form method="POST" action="reset_password.php" name="reset_password">
            <input type="hidden" name="token_id" value="<?php print $token_id; ?>">
            <table>
                <tr>
                    <td>Nova palavra-passe: </td>
                    <td><input size="10" maxlength="250" type="password" name="password" autocomplete="new-password"
                            value="" required></td>
                </tr>
                <tr>
                    <td>Confirmar palavra-passe: </td>
                    <td><input size="10" maxlength="250" type="password" name="verify_password"
                            autocomplete="new-password" value="" required></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Repor"></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>