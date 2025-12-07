<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Config;
use PHPLedger\Util\Html;
use PHPLedger\Version;

class LoginView
{
    private ApplicationObjectInterface $app;

    /**
     * Renders the login view.
     *
     * @param array $data Data to be used in the view.
     */
    public function render(ApplicationObjectInterface $app, array $data): void
    {
        $this->app = $app;
        $postUser = htmlspecialchars($data['postUser'] ?? '');
        $userAuth = $data['userAuth'] ?? false;
        $expired = $data['expired'] ?? 0;
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title() ?></title>
            <?php Html::header(); ?>
        </head>

        <body onload="document.getElementById('username').focus();">
            <div id="login">
                <h1><?= htmlspecialchars(Config::get('title')) ?></h1>
                <form method="POST" action="?lang=<?= $this->app->l10n()->lang() ?>" name="login" autocomplete="off">
                    <input name="lang" value="<?= $this->app->l10n()->lang() ?>" type="hidden" />
                    <?= CSRF::inputField() ?>
                    <div id="content">
                        <p><input required size="25" maxlength="50" type="text" name="username" id="username"
                                placeholder="<?= $this->app->l10n()->l('username') ?>" autocomplete="username"
                                value="<?= $postUser ?>"></p>
                        <p><input required size="25" maxlength="255" type="password" name="password"
                                placeholder="<?= $this->app->l10n()->l('password') ?>" autocomplete="current-password"></p>
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userAuth): ?>
                            <p class="invalid-login"><?= $this->app->l10n()->l('invalid_credentials') ?></p>
                        <?php endif; ?>
                        <?php if ($expired): ?>
                            <p class="invalid-login"><?= $this->app->l10n()->l('expired_session') ?></p>
                        <?php endif; ?>
                        <p id="formButton"><input type="submit" value="<?= $this->app->l10n()->l('login') ?>"></p>
                        <p id="versionTagContent" class="version-tag">
                            <a href="https://github.com/aholiveira/phpledger"
                                aria-label="<?= $this->app->l10n()->l('version', Version::string()) ?>">
                                <?= $this->app->l10n()->l('version', Version::string()) ?>
                            </a>
                        </p>
                        <p id="languageSelector" class="version-tag"><small><?php Html::languageSelector(false); ?></small></p>
                    </div>
                </form>
            </div>
        </body>

        </html>
<?php
    }
}
