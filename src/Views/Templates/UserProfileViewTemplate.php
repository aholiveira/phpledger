<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class UserProfileViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle, $appTitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div class="header">
                </div>
                <main>
                    <div class="main config single_item" id="main">
                        <form method="POST">
                            <?= $csrf ?><input type="hidden" name="action" value="<?= $action ?>"><input type="hidden" name="lang" value="<?= $lang ?>"><input type="hidden" name="id" value="<?= $text['id'] ?>">
                            <p><label for="username"><?= $label['username'] ?></label><input type="text" id="username" name="username" value="<?= htmlspecialchars($text['username']) ?>" readonly></p>
                            <p><label for="firstName"><?= $label['first_name'] ?></label><input type="text" id="firstName" name="firstname" value="<?= htmlspecialchars($text['firstName']) ?>"></p>
                            <p><label for="lastName"><?= $label['last_name'] ?></label><input type="text" id="lastName" name="lastname" value="<?= htmlspecialchars($text['lastName']) ?>"></p>
                            <p><label for="fullName"><?= $label['display_name'] ?></label><input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($text['fullName']) ?>"></p>
                            <p><label for="email"><?= $label['email'] ?></label><input type="text" id="email" name="email" value="<?= htmlspecialchars($text['email']) ?>"></p>
                            <p><label for="password"><?= $label['password'] ?></label><input type="password" id="password" name="password" value=""></p>
                            <p><label for="verifyPassword"><?= $label['verify_password'] ?></label><input type="password" id="verifyPassword" name="verifyPassword" autocomplete="new-password" value=""></p>
                            <p><button type="submit" style="grid-column: 2 / 2;" id="submitButton" name="itemaction" value="save"><?= $label['save'] ?></button></p>
                            <p id="errorMsg" style="color:red; grid-column: 2 / 2"><?= $message ?></p>
                        </form>
                    </div>
                </main>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>
        <script>
            const password = document.getElementById('password');
            const verify = document.getElementById('verifyPassword');
            const submitBtn = document.getElementById('submitButton');
            const errorMsg = document.getElementById('errorMsg');

            function validatePasswords() {
                if (!password.value && !verify.value) {
                    submitBtn.disabled = false;
                    errorMsg.textContent = "";
                    return;
                }

                if (password.value && verify.value && password.value === verify.value) {
                    submitBtn.disabled = false;
                    errorMsg.textContent = "";
                } else {
                    submitBtn.disabled = true;
                    errorMsg.textContent = "As palavras-passe não coincidem";
                }
            }
            password.addEventListener('input', validatePasswords);
            verify.addEventListener('input', validatePasswords);
            password.addEventListener('change', validatePasswords);
            verify.addEventListener('change', validatePasswords);
            password.addEventListener('paste', () => setTimeout(validatePasswords, 0));
            verify.addEventListener('paste', () => setTimeout(validatePasswords, 0));
        </script>

        </html>
<?php
    }
}
