<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
require_once "prepend.php";
config::init(__DIR__ . '/config.json');

$object_factory = new object_factory();
$view_factory = new view_factory();
$data_storage = $object_factory->data_storage();
$update_result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('index.php');
    }
    $action = $_POST['action'] ?? null;
    if ($action === 'update_db') {
        $update_result = $data_storage->update();
    }
}

$needs_update = !$data_storage->check();
$message = nl2br(htmlspecialchars($data_storage->message(), ENT_QUOTES, 'UTF-8'));
$pagetitle = l10n::l('update_needed');
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php include "header.php"; ?>
    <title><?= $pagetitle ?></title>
    <?php if ($update_result): ?>
        <meta http-equiv="refresh" content="5;url=index.php">
    <?php endif; ?>
</head>

<body>
    <main class="maingrid">
        <div class="main update-screen">
            <?php include ROOT_DIR . "/lang_selector.php"; ?>
            <section id="update-messages" aria-live="polite">
                <?php if ($update_result === null): ?>
                    <?php if ($needs_update): ?>
                        <p><?= l10n::l('db_needs_update') ?></p>
                        <p><?= l10n::l('cannot_use_app') ?></p>
                        <p><?= l10n::l('start_update') ?></p>
                        <p><?= $message ?></p>
                        <form method="POST" role="form" aria-describedby="update-messages"
                            action="update.php?lang=<?= l10n::$lang ?>">
                            <?= CSRF::inputField() ?>
                            <button class="submit" type="submit" name="action" value="update_db"
                                aria-label="<?= l10n::l('do_update') ?>">
                                <?= l10n::l('do_update') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <p><?= l10n::l('db_ok') ?></p>
                        <p><?= l10n::l('go_login') ?> <a href="index.php"><?= l10n::l('login_screen') ?></a>.</p>
                    <?php endif; ?>
                <?php elseif ($update_result): ?>
                    <p><?= $message ?></p>
                    <p><?= l10n::l('db_updated') ?></p>
                    <p><?= l10n::l('redirecting') ?></p>
                <?php else: ?>
                    <p role="alert"><?= l10n::l('update_fail') ?></p>
                    <p><?= l10n::l('error_msg') ?><br><?= $message ?></p>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>

</html>