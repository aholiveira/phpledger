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
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;
use PHPLedger\Views\ViewFactory;

class EntryCategoryListView
{
    private ApplicationObjectInterface $app;

    private $pagetitle = "Tipos de movimentos";

    public function render(ApplicationObjectInterface $app, string $message, bool $success): void
    {
        $this->app = $app;
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($this->pagetitle) ?></title>
            <?php Html::header(); ?>
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
                    <p style="margin: 0"><a href="index.php?action=entry_type">Adicionar</a></p>
                </div>
                <div id="main" class="main">
                    <div class="entry_category_list">
                        <?php
                        $object = ObjectFactory::entryCategory();
                        $viewer = ViewFactory::instance()->entryCategoryView($this->app, $object);
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
