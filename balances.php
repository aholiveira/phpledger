<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Saldos";
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php
        include ROOT_DIR . "/menu_div.php";
        ?>
        <div class="header" style="height: 0;"></div>
        <div class="main" id="main">
            <div class="saldos">
                <?php
                $object = $object_factory->account();
                $viewer = $view_factory->account_balance_view($object);
                print $viewer->printObjectList($object->getList(['activa' => ['operator' => '=', 'value' => '1']]));
                ?>
            </div>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>