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
            <div id="login">
                <h1><?= htmlspecialchars($pagetitle) ?></h1>
                <form method="POST" action="?lang=<?= $lang ?>" name="login" autocomplete="off">
                    <input name="lang" value="<?= $lang ?>" type="hidden" />
                    <?= $csrf ?>
                    <div id="content">
                        <p><input required size="25" maxlength="50" type="text" name="username" id="username"
                                placeholder="<?= $label['username'] ?>" autocomplete="username"
                                value="<?= htmlspecialchars($postUser) ?>"></p>
                        <p><input required size="25" maxlength="255" type="password" name="password"
                                placeholder="<?= $label['password'] ?>" autocomplete="current-password"></p>
                        <?php if ($errorMessage !== ''): ?>
                            <p class="invalid-login"><?= $errorMessage ?></p>
                        <?php endif; ?>
                        <p id="formButton"><input type="submit" value="<?= $label['login'] ?>"></p>
                        <p id="versionTagContent" class="version-tag">
                            <a href="https://github.com/aholiveira/phpledger"
                                aria-label="<?= $footer['versionText'] ?>">
                                <?= $footer['versionText'] ?>
                            </a>
                        </p>
                        <p id="languageSelector" class="version-tag"><small><?= $footer['languageSelectorHtml'] ?></small></p>
                    </div>
                </form>
            </div>
        </body>

        </html>
<?php
    }
}
