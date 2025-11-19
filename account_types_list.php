<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include_once __DIR__ . "/contas_config.php";
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Views\ViewFactory;

$pagetitle = "Tipos de contas";

?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header($pagetitle); ?>
</head>

<body>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php Html::menu(); ?>
        <div class="header">
            <p style="margin: 0"><a href="account_types.php">Adicionar</a></p>
        </div>
        <div class="main" id="main">
            <?php
            $object = ObjectFactory::accounttype();
            $viewer = ViewFactory::instance()->accountTypeView($object);
            print $viewer->printObjectList($object->getList());
            ?>
        </div>
        <?php Html::footer(); ?>
    </div>
    <script>
        setTimeout(() => { document.getElementById("preloader").style.display = "none"; }, 0);
    </script>
</body>

</html>
