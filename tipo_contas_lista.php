<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include ROOT_DIR . "/contas_config.php";
$pagetitle = "Tipos de contas";

$sql = "SELECT tipo_id, tipo_desc " .
    "FROM tipo_contas " .
    "ORDER BY tipo_id";
?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <div class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        ?>
        <div class="header">
            <p style="margin: 0"><a href="tipo_contas.php">Adicionar</a></p>
        </div>
        <div class="main" id="main">
            <?php
            $object = $object_factory->accounttype();
            $viewer = $view_factory->account_type_view($object);
            print $viewer->printObjectList($object->getAll());
            ?>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>