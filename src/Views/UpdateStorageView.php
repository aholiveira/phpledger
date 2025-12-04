<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views;

use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class UpdateStorageView
{
    public function render(string $pagetitle, bool $needsUpdate, ?bool $updateResult, string $message): void
    {
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html() ?>">

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
                                <p><?= L10n::l('db_needs_update') ?></p>
                                <p><?= L10n::l('cannot_use_app') ?></p>
                                <p><?= L10n::l('start_update') ?></p>
                                <p><?= $message ?></p>
                                <form method="POST" aria-describedby="update-messages" action="index.php?action=update&lang=<?= L10n::$lang ?>">
                                    <?= CSRF::inputField() ?>
                                    <button class="submit" type="submit" name="action" value="update_db"
                                        aria-label="<?= L10n::l('do_update') ?>">
                                        <?= L10n::l('do_update') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <p><?= L10n::l('db_ok') ?></p>
                                <p><?= L10n::l('go_login') ?> <a href="index.php" aria-label="<?= L10n::l('login_screen') ?>"><?= L10n::l('login_screen') ?></a>.</p>
                            <?php endif; ?>
                        <?php elseif ($updateResult): ?>
                            <p><?= $message ?></p>
                            <p><?= L10n::l('db_updated') ?></p>
                            <p><?= L10n::l('redirecting') ?></p>
                        <?php else: ?>
                            <p role="alert"><?= L10n::l('update_fail') ?></p>
                            <p><?= L10n::l('error_msg') ?><br><?= $message ?></p>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        </body>

        </html>
<?php

    }
}
