<?php

namespace PHPLedger\Views;

use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class ApplicationErrorView
{
    public function render(string $message): void
    {
        $pagetitle = L10n::l("Application error");
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <main>
                    <div class="main" id="main">
                        <p>Application error: <?= htmlspecialchars($message); ?>
                        <p>Check your config.json file</p>
                    </div>
                </main>
            </div>
        </body>

        </html>

<?php
    }
}
