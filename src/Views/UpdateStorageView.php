<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;

class UpdateStorageView
{
    private ApplicationObjectInterface $app;
    public function render(ApplicationObjectInterface $app, string $pagetitle, bool $needsUpdate, ?bool $updateResult, string $message): void
    {
        $this->app = $app;
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <main class="maingrid">
                <div class="main update-screen">
                    <?php Html::languageSelector(); ?>
                    <section id="update-messages" aria-live="polite">
                        <?php if ($updateResult === null): ?>
                            <?php if ($needsUpdate): ?>
                                <p><?= $this->app->l10n()->l('db_needs_update') ?></p>
                                <p><?= $this->app->l10n()->l('cannot_use_app') ?></p>
                                <p><?= $this->app->l10n()->l('start_update') ?></p>
                                <p><?= $message ?></p>
                                <form method="POST" aria-describedby="update-messages" action="index.php?action=update&lang=<?= $this->app->l10n()->lang() ?>">
                                    <?= CSRF::inputField() ?>
                                    <button class="submit" type="submit" name="action" value="update_db"
                                        aria-label="<?= $this->app->l10n()->l('do_update') ?>">
                                        <?= $this->app->l10n()->l('do_update') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <p><?= $this->app->l10n()->l('db_ok') ?></p>
                                <p><?= $this->app->l10n()->l('go_login') ?> <a href="index.php" aria-label="<?= $this->app->l10n()->l('login_screen') ?>"><?= $this->app->l10n()->l('login_screen') ?></a>.</p>
                            <?php endif; ?>
                        <?php elseif ($updateResult): ?>
                            <p><?= $message ?></p>
                            <p><?= $this->app->l10n()->l('db_updated') ?></p>
                            <p><?= $this->app->l10n()->l('redirecting') ?></p>
                        <?php else: ?>
                            <p role="alert"><?= $this->app->l10n()->l('update_fail') ?></p>
                            <p><?= $this->app->l10n()->l('error_msg') ?><br><?= $message ?></p>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        </body>

        </html>
<?php

    }
}
