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

class EntryCategoryFormView
{
    private ApplicationObjectInterface $app;

    public function render(ApplicationObjectInterface $app, string $form, bool $new): void
    {
        $this->app = $app;
        $pagetitle = "Tipo de movimentos";
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <?php Html::menu(); ?>
                <div class="header" style="height: 0;"></div>
                <div id="main" class="main">
                    <form method="POST" action="index.php?action=entry_types">
                        <input type="hidden" name="action" value="entry_types">
                        <table class="entry_category">
                            <?= $form ?>
                            <tr>
                                <td><input type="submit" name="update" value="Gravar"></td>
                                <?php if (!$new): ?>
                                    <td><input type="submit" name="update" value="Apagar"></td>
                                <?php endif ?>
                            </tr>
                        </table>
                    </form>
                </div>
                <?php Html::footer(); ?>
            </div>
        </body>

        </html>
<?php
    }
}
