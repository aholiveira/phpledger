<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include_once __DIR__ . "/contas_config.php";
$pagetitle = "Tipos de contas";

?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php include_once "header.php"; ?>
</head>

<body>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php
        include_once ROOT_DIR . "/menu_div.php";
        ?>
        <div class="header">
            <p style="margin: 0"><a href="account_types.php">Adicionar</a></p>
        </div>
        <div class="main" id="main">
            <?php
            $object = $object_factory->accounttype();
            $viewer = $view_factory->account_type_view($object);
            print $viewer->printObjectList($object->getList());
            ?>
        </div>
        <?php include_once "footer.php"; ?>
    </div>
    <script>
        setTimeout(() => { document.getElementById("preloader").style.display = "none"; }, 0);
    </script>
</body>

</html>