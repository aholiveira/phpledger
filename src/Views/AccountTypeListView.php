<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;

final class AccountTypeListView
{
    private ApplicationObjectInterface $app;

    public function render(ApplicationObjectInterface $app, array $data): void
    {
        $this->app = $app;
        $list = $data['list'];
        $lang = $data['lang'];
        $pagetitle = $this->app->l10n()->l("account_types");
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html(); ?>">

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
                <div class="header">
                    <p style="margin:0"><a href="index.php?action=account_type&lang=<?= $lang ?>">Adicionar<?php $this->app->l10n()->pl("Adicionar"); ?></a></p>
                </div>
                <div class="main" id="main">
                    <?php
                    $viewer = ViewFactory::instance()->accountTypeView($this->app, ObjectFactory::accounttype());
                    print $viewer->printObjectList($list);
                    ?>
                </div>
                <?php Html::footer($this->app, $data['action']); ?>
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
