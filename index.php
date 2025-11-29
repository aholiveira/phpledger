<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
require_once __DIR__ . '/prepend.php';

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Logger;
use PHPLedger\Util\Redirector;
use PHPLedger\Util\SessionManager;
use PHPLedger\Version;

$userAuth = false;
$postUser = "";
$postPass = "";
$inputFilter = [
    'username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'password' => FILTER_UNSAFE_RAW,
    '_csrf_token' => FILTER_UNSAFE_RAW
];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['do_logout'])) {
    SessionManager::start();
    $defaults = ObjectFactory::defaults()::getByUsername($_SESSION['user']);
    if ($defaults !== null) {
        $defaults->lastVisited = "";
        $defaults->update();
    }
    SessionManager::logout();
    Redirector::to("index.php");
}
# Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionManager::start();
    $filtered = filter_input_array(INPUT_POST, $inputFilter, true);
    if (!CSRF::validateToken($filtered['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to("index.php");
        exit("Invalid CSRF token");
    }
    $postUser = trim($filtered['username'] ?? '');
    $postPass = $filtered['password'] ?? '';
    if (!empty($postUser)) {
        $user = ObjectFactory::user()::getByUsername($postUser);
        $userAuth = $user->verifyPassword($postPass);
        if ($userAuth) {
            session_regenerate_id(true);
            SessionManager::refreshExpiration();
            $_SESSION['user'] = $postUser;
            $defaults = ObjectFactory::defaults()::getByUsername($postUser) ?? ObjectFactory::defaults()::init();
            $defaults->entryDate = date("Y-m-d");
            $defaults->language = L10n::$lang;
            Logger::instance()->info("User [$postUser] logged in");
            $target = $defaults !== null ?
                $defaults->lastVisited :
                sprintf(
                    "ledger_entries.php?lang=%s&filter_sdate=%s",
                    L10n::$lang,
                    date('Y-m-01')
                );
            Logger::instance()->debug("Redirecting to [{$target}]");
            Redirector::to($target);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header(); ?>
</head>

<body onload="document.getElementById('username').focus();">
    <div id="login">
        <h1><?= htmlspecialchars(config::get("title")) ?></h1>
        <form method="POST" action="?lang=<?= l10n::$lang ?>" name="login" autocomplete="off">
            <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
            <?= CSRF::inputField() ?>
            <div id="content">
                <p><input required="" aria-required="true" size="25" maxlength="50" type="text" name="username"
                        id="username" placeholder="<?= l10n::l('username') ?>" autocomplete="username"
                        value="<?= htmlspecialchars($postUser) ?>"></p>
                <p><input required size="25" maxlength="255" type="password" name="password"
                        placeholder="<?= l10n::l('password') ?>" autocomplete="current-password" value=""></p>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userAuth): ?>
                    <p class="invalid-login" style="width: 100%"><?= l10n::l('invalid_credentials') ?></p>
                <?php endif; ?>
                <?php if ($_REQUEST['expired'] ??= 0): ?>
                    <p class="invalid-login" style="width: 100%"><?= l10n::l('expired_session') ?></p>
                <?php endif; ?>
                <p id="formButton">
                    <input type="submit" value="<?= l10n::l('login') ?>">
                </p>
                <p id="versionTagContent" class="version-tag">
                    <a href="https://github.com/aholiveira/phpledger"
                        aria-label="<?= l10n::l('version', Version::string()) ?>"><?= l10n::l('version', Version::string()) ?></a>
                </p>
                <p id="languageSelector" class="version-tag"><small><?php Html::languageSelector(false); ?></small></p>
            </div>
        </form>
    </div>
</body>

</html>
