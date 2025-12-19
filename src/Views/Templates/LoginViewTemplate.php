<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class LoginViewTemplate extends AbstractViewTemplate
{
    /**
     * Renders the login view.
     *
     * @param array $data Data to be used in the view.
     */
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title() ?></title>
            <?php Html::header(); ?>
        </head>

        <body onload="document.getElementById('username').focus();">
            <div id="login" class="login-form">
                <h1><?= htmlspecialchars($pagetitle) ?></h1>
                <img src="assets/logo.png" width="64">
                <form method="POST" name="login" autocomplete="off">
                    <input name="lang" value="<?= $lang ?>" type="hidden" /><?= $csrf ?>
                    <input required="" maxlength="255" type="text" name="username" id="username" placeholder="<?= $label['username'] ?>" autocomplete="username" value="<?= htmlspecialchars($postUser) ?>">
                    <input required="" maxlength="255" type="password" name="password" id="password" placeholder="<?= $label['password'] ?>" autocomplete="current-password">
                    <?php if ($errorMessage !== ''): ?><p class="invalid-login"><?= $errorMessage ?></p><?php endif; ?>
                    <button type="submit" value="login" name="login"><?= $label['login'] ?></button>
                    <a class="version-tag" href="https://github.com/aholiveira/phpledger" aria-label="<?= $footer['versionText'] ?>"><?= $footer['versionText'] ?></a>
                    <small class="version-tag"><?= $footer['languageSelectorHtml'] ?></small>
                </form>
            </div>
        </body>

        </html>
<?php
    }
}
