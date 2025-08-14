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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? null) === 'Actualizar') {
    $update_result = $data_storage->update();
}

$needs_update = !$data_storage->check();
$message = nl2br(htmlspecialchars($data_storage->message(), ENT_QUOTES, 'UTF-8'));
$pagetitle = l10n::l('update_needed');
?>
<!DOCTYPE html>
<html lang="<?= l10n::$lang === 'en-us' ? 'en-US' : 'pt-PT' ?>">

<head>
    <?php include "header.php"; ?>
    <title><?= l10n::l('update_needed') ?></title>
    <?php if ($update_result): ?>
        <meta http-equiv="refresh" content="5;url=index.php">
    <?php endif; ?>
</head>

<body>
    <div class="maingrid">
        <div class="main update-screen">
            <div class="lang-selector">
                <?php if (l10n::$lang === 'pt-PT'): ?>
                    <a href="?lang=en-us">EN</a> | <span>PT</span>
                <?php else: ?>
                    <span>EN</span> | <a href="?lang=pt-pt">PT</a>
                <?php endif; ?>
            </div>
            <?php if ($update_result === null): ?>
                <?php if ($needs_update): ?>
                    <p><?= l10n::l('db_needs_update') ?></p>
                    <p><?= l10n::l('cannot_use_app') ?></p>
                    <p><?= l10n::l('start_update') ?></p>
                    <p><?= $message ?></p>
                    <form method="POST" action="update.php?lang=<?= $lang ?>">
                        <input class="submit" type="submit" name="action" value="<?= l10n::l('form_button') ?>">
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
                <p><?= l10n::l('update_fail') ?></p>
                <p><?= l10n::l('error_msg') ?><br><?= $message ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>