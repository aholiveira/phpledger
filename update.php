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

$strings = [
    'pt-PT' => [
        'title' => "Actualização necessária",
        'db_needs_update' => "A base de dados necessita de ser actualizada.",
        'cannot_use_app' => "Não poderá utilizar a aplicação sem concluir esta actualização.",
        'start_update' => "Prima o botão abaixo para iniciar o processo.",
        'form_button' => "Actualizar",
        'db_updated' => "A base de dados foi actualizada com sucesso.",
        'redirecting' => "Será redireccionado para o ecrã de login em 5 segundos.",
        'db_ok' => "A base de dados está actualizada.",
        'go_login' => "Pode aceder à aplicação a partir do <a style='padding: 0' href='index.php'>ecrã de login</a>.",
        'update_fail' => "Falha na actualização. Verifique as permissões do utilizador.",
        'error_msg' => "Mensagem de erro:"
    ],
    'en-US' => [
        'title' => "Update Required",
        'db_needs_update' => "The database requires an update.",
        'cannot_use_app' => "You cannot use the application until this update is completed.",
        'start_update' => "Click the button below to begin the update process.",
        'form_button' => "Update",
        'db_updated' => "The database was successfully updated.",
        'redirecting' => "You will be redirected to the login screen in 5 seconds.",
        'db_ok' => "The database is up to date.",
        'go_login' => "You can access the application from the <a style='padding: 0' href='index.php'>login screen</a>.",
        'update_fail' => "Update failed. Please check user permissions.",
        'error_msg' => "Error message:"
    ]
];
$pagetitle = $strings[$lang]['title'];
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'en' ? 'en-US' : 'pt-PT' ?>">

<head>
    <?php include "header.php"; ?>
    <title><?= $strings[$lang]['title'] ?></title>
    <?php if ($update_result): ?>
        <meta http-equiv="refresh" content="5;url=index.php">
    <?php endif; ?>
</head>

<body>
    <div class="maingrid">
        <div class="main update-screen">
            <div style="text-align: right; margin-bottom: 1em;">
                <?php if ($lang === 'pt'): ?>
                    <a href="?lang=en">EN</a> | <span>PT</span>
                <?php else: ?>
                    <span>EN</span> | <a href="?lang=pt">PT</a>
                <?php endif; ?>
            </div>
            <?php if ($update_result === null): ?>
                <?php if ($needs_update): ?>
                    <p><?= $strings[$lang]['db_needs_update'] ?></p>
                    <p><?= $strings[$lang]['cannot_use_app'] ?></p>
                    <p><?= $strings[$lang]['start_update'] ?></p>
                    <p><?= $message ?></p>
                    <form method="POST" action="update.php?lang=<?= $lang ?>">
                        <input class="submit" type="submit" name="action" value="<?= $strings[$lang]['form_button'] ?>">
                    </form>
                <?php else: ?>
                    <p><?= $strings[$lang]['db_ok'] ?></p>
                    <p><?= $strings[$lang]['go_login'] ?></p>
                <?php endif; ?>
            <?php elseif ($update_result): ?>
                <p><?= $message ?></p>
                <p><?= $strings[$lang]['db_updated'] ?></p>
                <p><?= $strings[$lang]['redirecting'] ?></p>
            <?php else: ?>
                <p><?= $strings[$lang]['update_fail'] ?></p>
                <p><?= $strings[$lang]['error_msg'] ?><br><?= $message ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
