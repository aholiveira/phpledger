<?php

namespace PHPLedger\Views;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

final class AccountTypeListView
{
    public function render(array $data): void
    {
        $list = $data['list'];
        $lang = $data['lang'];
        $pagetitle = L10n::l("account_types");
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html(); ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?= Html::header() ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu(); ?>
                <div class="header">
                    <p style="margin:0"><a href="account_types.php?lang=<?= $lang ?>"><?php L10n::pl("Adicionar"); ?></a></p>
                </div>
                <div class="main" id="main">
                    <?php
                    $viewer = ViewFactory::instance()->accountTypeView(ObjectFactory::accounttype());
                    print $viewer->printObjectList($list);
                    ?>
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
