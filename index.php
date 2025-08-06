<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
require_once __DIR__ . "/util/redirector.php";
require_once __DIR__ . "/util/sessionmanager.php";

const SESSION_TIMEOUT = 3600;
if (isset($_GET['do_logout']) && $_GET['do_logout'] === '1') {
    SessionManager::logout();
    Redirector::to('index.php');
}
if (!defined("ROOT_DIR")) {
    require_once "prepend.php";
}
config::init(__DIR__ . '/config.json');
$userauth = false;
$input_variables_filter = ['username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'password' => FILTER_UNSAFE_RAW];

$post_user = "";
$filtered_input = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('index.php');
    }
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    $post_user = trim($filtered_input["username"] ?? "");
    $post_pass = $filtered_input["password"] ?? "";
}
$object_factory = new object_factory();
$data_storage = $object_factory::data_storage();
if ($data_storage->check() === false) {
    Redirector::to('update.php', 1);
}
if (!empty($post_user)) {
    $userauth = authentication::authenticate($post_user, $post_pass);
    if ($userauth) {
        session_regenerate_id(true);
        $_SESSION['user'] = $post_user;
        $_SESSION['expires'] = time() + SESSION_TIMEOUT;
        $defaults = $object_factory->defaults()->getById(1);
        $defaults->entry_date = date("Y-m-d");
        $defaults->update();
        $target = "ledger_entries.php?filter_sdate=" . date('Y-m-01');
        if (isset($_COOKIE['current_url'])) {
            $url = rawurldecode($_COOKIE['current_url']);
            if (!empty($url)) {
                $target = $url;
            }
        }
        $logger->info("User [$post_user] logged in");
        Redirector::to($target);
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
        <h1><?= htmlspecialchars(config::get("title")) ?></h1>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" name="login" autocomplete="off">
            <?= CSRF::inputField() ?>
            <table>
                <tr>
                    <td><input size="25" maxlength="50" type="text" name="username" id="username"
                            placeholder="Utilizador" autocomplete="username"
                            value="<?= htmlspecialchars($post_user) ?>"></td>
                </tr>
                <tr>
                    <td><input size="25" maxlength="255" type="password" name="password" placeholder="Password"
                            autocomplete="current-password" value=""></td>
                </tr>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userauth): ?>
                    <tr>
                        <td class="invalid-login" style="width: 100%">Credenciais inv&aacute;lidas</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align: center"><input type="submit" value="Entrar"></td>
                </tr>
                <tr>
                    <td class="version-tag"><a href="https://github.com/aholiveira/phpledger">Version <?= VERSION ?></a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>