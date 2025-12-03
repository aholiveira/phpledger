<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;

require_once "prepend.php";
Config::init(__DIR__ . '/config.json');

$data_storage = ObjectFactory::dataStorage();
$update_result = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('index.php');
    }
    $action = $_POST['action'] ?? null;
    if ($action === 'update_db') {
        $update_result = $data_storage->update();
        if ($update_result) {
            if (!headers_sent()) {
                header("Refresh: 8; URL=index.php");
            }
        }
    }
}

$needs_update = !$data_storage->check();
$message = nl2br(htmlspecialchars($data_storage->message(), ENT_QUOTES, 'UTF-8'));
$pagetitle = L10n::l('update_needed');
?>
<!DOCTYPE html>
<html lang="<?= L10n::html() ?>">

<head>
    <title><?= Html::title($pagetitle) ?></title>
    <?= Html::header() ?>
</head>

<body>
    <main class="maingrid">
        <div class="main update-screen">
            <?php Html::languageSelector(); ?>
            <section id="update-messages" aria-live="polite">
                <?php if ($update_result === null): ?>
                    <?php if ($needs_update): ?>
                        <p><?= L10n::l('db_needs_update') ?></p>
                        <p><?= L10n::l('cannot_use_app') ?></p>
                        <p><?= L10n::l('start_update') ?></p>
                        <p><?= $message ?></p>
                        <form method="POST" aria-describedby="update-messages" action="update.php?lang=<?= L10n::$lang ?>">
                            <?= CSRF::inputField() ?>
                            <button class="submit" type="submit" name="action" value="update_db"
                                aria-label="<?= L10n::l('do_update') ?>">
                                <?= L10n::l('do_update') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <p><?= L10n::l('db_ok') ?></p>
                        <p><?= L10n::l('go_login') ?> <a href="index.php"
                                aria-label="<?= L10n::l('login_screen') ?>"><?= L10n::l('login_screen') ?></a>.</p>
                    <?php endif; ?>
                <?php elseif ($update_result): ?>
                    <p><?= $message ?></p>
                    <p><?= L10n::l('db_updated') ?></p>
                    <p><?= L10n::l('redirecting') ?></p>
                <?php else: ?>
                    <p role="alert"><?= L10n::l('update_fail') ?></p>
                    <p><?= L10n::l('error_msg') ?><br><?= $message ?></p>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>

</html>
