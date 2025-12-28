<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Views\Templates\AbstractViewTemplate;
use PHPLedger\Util\Html;
use PHPLedger\Util\SetupState;

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
                <div id="notification" class="notification <?= $success ? 'success' : 'fail' ?>">
                    <?= implode('<br>', array_map('htmlspecialchars', $messages)) ?>
                </div>
            <?php endif; ?>
            <div class="maingrid">
                <main class="update-screen">
                    <form method="POST" id="configForm" class="login-form" data-state="<?= $state->value ?>">
                        <?= $csrf ?>
                        <input type="hidden" name="ajax" value="0" id="ajaxField">
                        <input type="hidden" name="action" value="setup">
                        <div id="main" class="config">
                            <div id="config-required" class="config" style="display:<?= $state === SetupState::CONFIG_REQUIRED ? 'block' : 'none' ?>">
                                <?php $setupViewFormTemplate->render(compact('label', 'config', 'state')); ?>
                            </div>
                            <div id="create-step" class="main warning" style="display: <?= $state === SetupState::STORAGE_MISSING ? 'block' : 'none' ?>;">
                                <p><?= htmlspecialchars($label['storage_does_not_exist']) ?></p>
                                <button type=" submit" name="itemaction" value="create_storage">
                                    <?= htmlspecialchars($label['create_storage'] ?? 'Create storage') ?>
                                </button>
                            </div>
                            <div id="migration-step" class="main warning" style="display: <?= $state === SetupState::MIGRATIONS_PENDING ? 'block' : 'none' ?>;">
                                <p><?= htmlspecialchars($label['pending_db_migrations_detected']) ?></p>
                                <button type=" submit" name="itemaction" value="run_migrations">
                                    <?= htmlspecialchars($label['apply_migrations'] ?? 'Apply migrations') ?>
                                </button>
                            </div>
                            <div id="admin-create" class="main warning" style="display:<?= $state === SetupState::ADMIN_MISSING ? 'block' : 'none' ?>;">
                                <p><?= htmlspecialchars($label['no_admin_user_detected']) ?></p>
                                <button type="submit" name="itemaction" value="create_admin">
                                    <?= htmlspecialchars($label['create_admin_user'] ?? 'Create admin user') ?>
                                </button>
                            </div>
                            <div id="setup-complete" class="main config" style="display: <?= $state === SetupState::COMPLETE ? 'block' : 'none' ?>;">
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
