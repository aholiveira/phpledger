<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Views\Templates\AbstractViewTemplate;
use PHPLedger\Util\Html;

final class ConfigViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <?php if (!empty($messages)): ?>
                <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                    <?= htmlspecialchars(implode(", ", $messages)) ?>
                </div>
                <script>
                    setTimeout(() => {
                        const el = document.getElementById('notification');
                        el.classList.add('hide');
                        el.addEventListener('transitionend', () => el.remove(), {
                            once: true
                        });
                    }, 2500);
                </script>
            <?php endif; ?>
            <div class="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div class="header" style="height:0;"></div>
                <main>
                    <div class="main config" id="main">
                        <?php if ($hasPermission): ?>
                            <?php (new ConfigViewFormTemplate)->render(compact('label', 'config', 'csrf')); ?>
                        <?php endif; ?>
                    </div>
                </main>
                <?php $ui->footer($label, $footer); ?>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const storageSelect = document.querySelector('select[name="storage_type"]');
                    const mysqlSettings = document.getElementById('mysql-settings');
                    storageSelect.addEventListener('change', () => mysqlSettings.style.display = storageSelect.value === 'mysql' ? 'grid' : 'none');
                });
            </script>
        </body>

        </html>
<?php
    }
}
