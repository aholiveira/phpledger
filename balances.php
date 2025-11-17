<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include_once __DIR__ . "/contas_config.php";
use \PHPLedger\Storage\ObjectFactory;
use \PHPLedger\Util\Html;
use \PHPLedger\Util\L10n;

$pagetitle = "Saldos";
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header(); ?>
</head>

<body>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php Html::menu(); ?>
        <div class="header" style="height: 0;"></div>
        <div class="main" id="main">
            <div class="saldos">
                <?php
                $object = ObjectFactory::account();
                $viewer = $viewFactory->account_balance_view($object);
                print $viewer->printObjectList($object->getList(['activa' => ['operator' => '=', 'value' => '1']]));
                ?>
            </div>
        </div>
        <?php Html::footer(); ?>
    </div>
    <script>
        setTimeout(() => { document.getElementById("preloader").style.display = "none"; }, 0);
    </script>
</body>

</html>
