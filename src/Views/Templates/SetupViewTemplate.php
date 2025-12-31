<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Views\Templates\AbstractViewTemplate;
use PHPLedger\Util\Html;

final class SetupViewTemplate extends AbstractViewTemplate
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
            <?php if (!empty($messages)): ?>
                <div id="notification" role="alert" aria-live="assertive" class="notification <?= $success ? 'success' : 'fail' ?>">
                    <?= implode('<br>', array_map('htmlspecialchars', $messages)) ?>
                </div>
            <?php endif; ?>
            <div class="config">
                <main class="update-screen">
                    <form method="POST" id="configForm" class="login-form" data-state="<?= $state->value ?>">
                        <?= $csrf ?>
                        <input type="hidden" name="ajax" value="0" id="ajaxField">
                        <input type="hidden" name="action" value="setup">
                        <div id="main" class="main config">
                            <div id="config_required" class="setup-section">
                                <?php $setupViewFormTemplate->render(compact('label', 'config', 'state')); ?>
                            </div>
                            <div id="storage_missing" class="main warning setup-section">
                                <p><?= htmlspecialchars($label['storage_does_not_exist']) ?></p>
                                <button type=" submit" name="itemaction" value="create_storage"><?= htmlspecialchars($label['create_storage']) ?></button>
                            </div>
                            <div id="migrations_pending" class="main warning setup-section">
                                <p><?= htmlspecialchars($label['pending_db_migrations_detected']) ?></p>
                                <button type=" submit" name="itemaction" value="run_migrations"><?= htmlspecialchars($label['apply_migrations']) ?></button>
                            </div>
                            <div id="admin_missing" class="main warning setup-section">
                                <p><?= htmlspecialchars($label['no_admin_user_detected']) ?></p>
                                <button type="submit" name="itemaction" value="create_admin"><?= htmlspecialchars($label['create_admin_user']) ?></button>
                            </div>
                            <div id="complete" class="main config setup-section">
                                <p><?= htmlspecialchars($label['setup_complete']) ?></p>
                                <a href="index.php" aria-label="<?= htmlspecialchars($label['login_page']) ?>"><?= htmlspecialchars($label['login_page']) ?></a>
                            </div>
                        </div>
                    </form>
                </main>
            </div>
            <script src="assets/js/config.js" defer></script>
        </body>

        </html>
<?php
    }
}
