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
use PHPLedger\Util\Html;

class BalancesView
{
    private ApplicationObjectInterface $app;

    public function render(ApplicationObjectInterface $app, string $reportData, string $action): void
    {
        $this->app = $app;
        $pagetitle = "Saldos";
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div class="header" style="height: 0;"></div>
                <div class="main" id="main">
                    <div class="saldos">
                        <?= $reportData ?>
                    </div>
                </div>
                <?php Html::footer($this->app, $action); ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById("preloader").style.display = "none";
                }, 0);
            </script>
        </body>

        </html>
<?php
    }
}
