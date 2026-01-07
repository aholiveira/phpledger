<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

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
        <html lang="<?= $htmlLang ?>">

        <head>
            <?php Html::header(); ?>
            <title><?= Html::title('', $appTitle) ?></title>
        </head>

        <body class="login-page">
            <main class="login-box">
                <h1 id="login-title"><?= $this->htmlSafe($pagetitle) ?></h1>
                <img class="login-logo" width="64" height="64" src="assets/media/logo-2-64x64.png" srcset="assets/media/logo-2-128x128.png 2x" alt="<?= $pagetitle ?> logo" loading="eager">
                <form class="login-form" aria-labelledby="login-title" method="POST" name="login" autocomplete="on" novalidate>
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <?= $csrf ?>
                    <input required="" maxlength="255" type="text" name="username" id="username" placeholder="<?= $label['username'] ?>" autocomplete="username" value="<?= $this->htmlSafe($postUser) ?>">
                    <input required="" maxlength="255" type="password" name="password" id="password" placeholder="<?= $label['password'] ?>" autocomplete="current-password">
                    <?php if ($errorMessage !== ''): ?><p class="login-message" role="alert" aria-live="polite"><?= $errorMessage ?></p><?php endif; ?>
                    <button type="submit" value="login" name="login"><?= $label['login'] ?></button>
                    <footer class="login-footer">
                        <a class="version-tag" href="https://github.com/aholiveira/phpledger" aria-label="<?= $footer['versionText'] ?>"><?= $footer['versionText'] ?></a>
                        <small class="version-tag"><?= $footer['languageSelectorHtml'] ?></small>
                    </footer>
                </form>
            </main>
            <script>
                document.getElementById('username').focus();
            </script>
        </body>

        </html>
<?php
    }
}
