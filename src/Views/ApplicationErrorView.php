<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\Html;

class ApplicationErrorView
{
    private ApplicationObjectInterface $app;
    public function render(ApplicationObjectInterface $app, string $message): void
    {
        $this->app = $app;
        $pagetitle = $this->app->l10n()->l("Application error");
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

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
