<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
// Determine if connection is secure for cookie params
if (!defined("ROOT_DIR")) {
    include "prepend.php";
}
config::init(__DIR__ . '/config.json');
if (isset($_GET['do_logout']) && $_GET['do_logout'] === '1') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: index.php');
    exit();
}

$userauth = false;
$input_variables_filter = ['username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'password' => FILTER_UNSAFE_RAW];

$post_user = "";
$filtered_input = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    $post_user = trim($filtered_input["username"] ?? "");
    $post_pass = $filtered_input["password"] ?? "";
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        if (!headers_sent()) {
            header('Location: index.php');
        } else {
            print '<meta http-equiv="REFRESH" content="0; URL=index.php">';
        }
        exit('Invalid CSRF token');
    }
}
$object_factory = new object_factory();
$data_storage = $object_factory::data_storage();
if ($data_storage->check() === false) {
    if (!headers_sent()) {
        header('Location: update.php');
    } else {
        print '<meta http-equiv="REFRESH" content="1; URL=update.php">';
    }
    exit();
}
if (!empty($post_user)) {
    $userauth = authentication::authenticate($post_user, $post_pass);
    if ($userauth) {
        //session_regenerate_id(true);
        $_SESSION['user'] = $post_user;
        $_SESSION['expires'] = time() + 3600;
        //session_write_close();
        $defaults = $object_factory->defaults()->getById(1);
        $defaults->entry_date = date("Y-m-d");
        $defaults->update();
        if (!headers_sent()) {
            header("Location: ledger_entries.php?filter_sdate=" . date('Y-m-01'), true, 303);
        } else {
            print '<meta http-equiv="REFRESH" content="0; URL=ledger_entries.php?filter_sdate=' . date('Y-m-01') . '">';
            print '<noscript><a href="ledger_entries.php?filter_sdate=' . date('Y-m-01') . '">Clique aqui para continuar</a></noscript>';
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body onload="document.getElementById('username').focus();">
    <div id="login">
        <h1><?php print htmlspecialchars(config::get("title")); ?></h1>
        <p>Introduza o seu nome de utilizador e password para entrar na aplica&ccedil;&atilde;o.</p>
        <?php if (isset($_POST['username']) && !$userauth): ?>
            <p class="invalid-login">Utilizador e/ou password inv&aacute;lidos</p>
        <?php endif; ?>
        <form method="POST" action="index.php" name="login" autocomplete="off">
            <?= CSRF::inputField() ?>
            <table>
                <tr>
                    <td>Utilizador:</td>
                    <td><input size="10" maxlength="50" type="text" name="username" id="username"
                            autocomplete="username" value="<?php print htmlspecialchars($post_user); ?>"></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td><input size="10" maxlength="255" type="password" name="password" autocomplete="current-password"
                            value=""></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center"><input type="submit" value="Entrar"></td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>