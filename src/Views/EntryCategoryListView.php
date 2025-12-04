<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Views\ViewFactory;

class EntryCategoryListView
{
    private $pagetitle = "Tipos de movimentos";

    public function render(string $message, bool $success): void
    {
?>
        <!DOCTYPE html>
        <html lang="<?= l10n::html() ?>">

        <head>
            <title><?= Html::title($this->pagetitle) ?></title>
            <?= Html::header(); ?>
        </head>

        <body>
            <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                <?= $message ?>
            </div>
            <div id="maingrid" class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu(); ?>
                <div class="header">
                    <p style="margin: 0"><a href="entry_type.php">Adicionar</a></p>
                </div>
                <div id="main" class="main">
                    <div class="entry_category_list">
                        <?php
                        $object = ObjectFactory::entryCategory();
                        $viewer = ViewFactory::instance()->entryCategoryView($object);
                        print $viewer->printObjectList($object->getList());
                        ?>
                    </div>
                </div>
                <?php Html::footer(); ?>
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
