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

class ReportYearView
{
    private string $pagetitle = "Relatório anual";
    private ApplicationObjectInterface $app;

    public function render(ApplicationObjectInterface $app, string $action, int $firstYear, int $lastYear, ReportYearHtmlView $reportHtml)
    {
        $this->app = $app;
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($this->pagetitle) ?></title>
            <?php Html::header(); ?>
            <script>
                function toogleGroup(groupName) {
                    var i, j, row, multiplier;
                    row = document.getElementsByClassName(groupName);
                    for (i = 0; i < row.length; i++) {
                        if (row[i].style.display == "none") {
                            row[i].style.removeProperty('display');
                        } else {
                            row[i].style.display = "none";
                        }
                    }
                    updateRowColors("report");
                }
            </script>
            <script type="text/javascript" src="assets/common.js"></script>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div id="header" class="header main config">
                    <form name="filtro" method="GET">
                        <input type="hidden" name="action" value="<?= $action ?>">
                        <p><label for="firstYear">Ano inicial</label><input type="text" id="firstYear" name="firstYear" maxlength="4" size="6" value="<?= $firstYear; ?>"></p>
                        <p><label for="lastYear">Ano final</label><input type="text" id="lastYear" name="lastYear" maxlength="4" size="6" value="<?= $lastYear; ?>"></p>
                        <p><input type="submit" value="Obter"></p>
                    </form>
                </div>
                <div class="main" id="main">
                    <div class="report_year">
                        <table class="lista report_year" id="report">
                            <?= $reportHtml->printAsTable(); ?>
                        </table>
                    </div>
                </div>
                <script type="text/javascript">
                    updateRowColors("report");
                </script>
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
