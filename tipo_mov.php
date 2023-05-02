<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Tipo de movimentos";
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
        <div class="header" style="height: 0;"></div>
        <div id="main" class="main">
            <form method="POST" action="tipo_mov_lista.php">
                <table class="entry_category">
                    <?php
                    $object = $object_factory->entry_category();
                    if (filter_has_var(INPUT_GET, "tipo_id")) {
                        $tipo_id = filter_input(INPUT_GET, "tipo_id", FILTER_VALIDATE_INT);
                        if ($tipo_id > 0) {
                            $object->getById($tipo_id);
                        }
                    }
                    $viewer = $view_factory->entry_category_view($object);
                    print $viewer->printForm();
                    ?>

                    <tr>
                        <td><input type="submit" name="update" value="Gravar" /></td>
                        <?php
                        if ($object->id != 0) {
                        ?>
                            <td><input type="submit" name="update" value="Apagar" /></td>
                        <?php
                        }
                        ?>
                    </tr>
                </table>
            </form>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>