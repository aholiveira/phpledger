<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
require_once __DIR__ . '/vendor/autoload.php';

use \PHPLedger\Storage\ObjectFactory;
use \PHPLedger\Util\Config;
use \PHPLedger\Util\CSRF;
use \PHPLedger\Util\Html;
use \PHPLedger\Util\L10n;
use \PHPLedger\Util\Logger;
use \PHPLedger\Util\Redirector;
use \PHPLedger\Util\SessionManager;

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

$postUser = "";
$filtered_input = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('index.php');
    }
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, true);
    $postUser = trim($filtered_input["username"] ?? "");
    $postPass = $filtered_input["password"] ?? "";
}
$dataStorage = ObjectFactory::dataStorage();
if ($dataStorage->check() === false) {
    Redirector::to("update.php?lang=" . l10n::$lang, 1);
}
if (!empty($postUser)) {
    $userAuth = ObjectFactory::user()::getByUsername($postUser)->verifyPassword($postPass);
    if ($userAuth) {
        session_regenerate_id(true);
        $_SESSION['user'] = $postUser;
        $_SESSION['expires'] = time() + SESSION_TIMEOUT;
        $defaults = ObjectFactory::defaults()->getById(1);
        $defaults->entry_date = date("Y-m-d");
        $defaults->language = l10n::$lang;
        $defaults->update();
        $target = sprintf("ledger_entries.php?lang=%s&filter_sdate=%s", l10n::$lang, date('Y-m-01'));
        Logger::instance()->info("User [$postUser] logged in");
        Redirector::to($target);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header($pagetitle); ?>
</head>

<body onload="document.getElementById('username').focus();">

    <div id="login">
        <h1><?= htmlspecialchars(config::get("title")) ?></h1>
        <form method="POST" action="?lang=<?= l10n::$lang ?>" name="login" autocomplete="off">
            <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
            <?= CSRF::inputField() ?>
            <table>
                <tr>
                    <td><input required size="25" maxlength="50" type="text" name="username" id="username"
                            placeholder="<?= l10n::l('username') ?>" autocomplete="username"
                            value="<?= htmlspecialchars($postUser) ?>"></td>
                </tr>
                <tr>
                    <td><input required size="25" maxlength="255" type="password" name="password"
                            placeholder="<?= l10n::l('password') ?>" autocomplete="current-password" value=""></td>
                </tr>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userauth): ?>
                    <tr>
                        <td class="invalid-login" style="width: 100%"><?= l10n::l('invalid_credentials') ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align: center"><input type="submit" value="<?= l10n::l('login') ?>"></td>
                </tr>
                <tr>
                    <td class="version-tag">
                        <a href="https://github.com/aholiveira/phpledger"><?= l10n::l('version', VERSION) ?></a>
                    </td>
                </tr>
                <tr>
                    <td class="version-tag">
                        <?php Html::languageSelector(); ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>

</html>
