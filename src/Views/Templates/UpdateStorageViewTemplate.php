<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class UpdateStorageViewTemplate extends AbstractViewTemplate
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
            <main class="maingrid">
                <div class="main update-screen">
                    <?= $footer['languageSelectorHtml'] ?>
                    <section id="update-messages" aria-live="polite">
                        <?php
                        switch ($showSection) {
                            case 'needs_update':
                        ?>
                                <p><?= $label['db_needs_update'] ?></p>
                                <p><?= $label['cannot_use_app'] ?></p>
                                <p><?= $label['start_update'] ?></p>
                                <p><?= nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ?></p>
                                <form method="POST" aria-describedby="update-messages" action="index.php?action=update&lang=<?= $lang ?>">
                                    <?= $csrf ?>
                                    <button class="submit" type="submit" name="action" value="update"
                                        aria-label="<?= $label['do_update'] ?>">
                                        <?= $label['do_update'] ?>
                                    </button>
                                </form>
                            <?php
                                break;
                            case 'storage_is_ok':
                            ?>
                                <p><?= $label['db_ok'] ?></p>
                                <p><?= $label['go_login'] ?> <a href="index.php" aria-label="<?= $label['login_screen'] ?>"><?= $label['login_screen'] ?></a>.</p>
                            <?php
                                break;
                            case 'update_success':
                            ?>
                                <p><?= nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ?></p>
                                <p><?= $label['db_updated'] ?></p>
                                <p><?= $label['redirecting'] ?></p>
                            <?php
                                break;
                            default:
                            ?>
                                <p role="alert"><?= $label['update_fail'] ?></p>
                                <p><?= $label['error_msg'] ?><br><?= nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php
                                break;
                        }
                        ?>
                    </section>
                </div>
            </main>
        </body>

        </html>
<?php

    }
}
