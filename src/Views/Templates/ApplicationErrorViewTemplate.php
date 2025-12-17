<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class ApplicationErrorViewTemplate extends AbstractViewTemplate
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
            <div class="maingrid">
                <main>
                    <div class="main" id="main">
                        <p>Application error: <?= htmlspecialchars($message) ?></p>
                        <p>Check your config.json file</p>
                    </div>
                </main>
            </div>
        </body>

        </html>
<?php
    }
}
