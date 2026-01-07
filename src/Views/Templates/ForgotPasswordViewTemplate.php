<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class ForgotPasswordViewTemplate extends AbstractViewTemplate
{

    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($label['password_recovery'], $appTitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body onload="document.getElementById('username').focus();">
            <?php
            ?>
            <div id="login">
                <h1><?= $this->htmlSafe($appTitle ?? "") ?></h1>
                <p><?= $this->htmlSafe($label['password_recovery']) ?></p>
                <div class="main form config">
                    <p class='error'><?= $message; ?></p>
                    <form method="POST" name="forgot_password">
                        <?= $csrf ?>
                        <input type="hidden" name="action" value="<?= $action ?>">
                        <p><label for="username"><?= $this->htmlSafe($label['username']) ?></label><input id="username" size="50" maxlength="250" type="text" name="username" value="" required></p>
                        <p><label for="email"><?= $this->htmlSafe($label['email']) ?></label><input id="email" size="50" maxlength="250" type="text" name="email" value="" required></p>
                        <p style="text-align: center"><input type="submit" value="<?= $label['send_reset_link'] ?>"></p>
                    </form>
                    <p id="languageSelector" class="version-tag"><small><?= $footer['languageSelectorHtml'] ?></small></p>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}
