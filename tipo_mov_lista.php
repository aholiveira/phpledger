<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Tipos de movimentos";

if ($_SERVER["REQUEST_METHOD"] == "POST" && filter_has_var(INPUT_POST, "update")) {
    $object = $object_factory->entry_category();
    $retval = false;
    $update = filter_input(INPUT_POST, "update", FILTER_DEFAULT);
    if (strcasecmp($update, "gravar") == 0) {
        $action = "gravado";
        $object->id = filter_input(INPUT_POST, "tipo_id", FILTER_VALIDATE_INT);
        $object->description = filter_input(INPUT_POST, "tipo_desc", FILTER_DEFAULT);
        $object->parent_id = null;
        if (filter_has_var(INPUT_POST, "parent_id")) {
            $parent_id = filter_input(INPUT_POST, "parent_id", FILTER_DEFAULT);
            if (strcasecmp($parent_id, "NULL") != 0) {
                $object->parent_id = $parent_id;
            }
        }
        if ($object->parent_id == $object->id) {
            Html::myalert("N&atilde;o pode colocar uma categoria como ascendente dela propria!");
        }
        $object->active = 0;
        if (filter_has_var(INPUT_POST, "active")) {
            $active = filter_input(INPUT_POST, "active", FILTER_DEFAULT);
            if (strcasecmp($active, "on") == 0) {
                $object->active = 1;
            }
        }
        if ($object->validate()) {
            $retval = $object->save();
        } else {
            Html::myalert("Dados inv&aacute;lidos. Por favor verifique.");
        }
    }
    if (strcasecmp($update, "apagar") == 0) {
        $action = "eliminado";
        if (filter_has_var(INPUT_POST, "tipo_id")) {
            $object->id = filter_input(INPUT_POST, "tipo_id", FILTER_VALIDATE_INT);
            $retval = $object->delete();
        }
    }
    if (!$retval) {
        Html::myalert("Ocorreu um erro na operacao");
    } else {
        Html::myalert("Registo {$action}. ID: {$object->id}");
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <div id="maingrid" class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        ?>
        <div class="header">
            <p style="margin: 0"><a href="tipo_mov.php">Adicionar</a></p>
        </div>
        <div id="main" class="main">
            <?php
            $object = $object_factory->entry_category();
            $viewer = $view_factory->entry_category_view($object);
            print $viewer->printObjectList($object->getAll());
            ?>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>