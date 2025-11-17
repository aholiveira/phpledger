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
use \PHPLedger\Views\ViewFactory;
$pagetitle = "Tipo de movimentos";
?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header(); ?>
</head>

<body>
    <div class="maingrid">
        <?php Html::menu(); ?>
        <div class="header" style="height: 0;"></div>
        <div id="main" class="main">
            <form method="POST" action="entry_types_list.php">
                <table class="entry_category">
                    <?php
                    $object = ObjectFactory::EntryCategory();
                    if (filter_has_var(INPUT_GET, "tipo_id")) {
                        $tipo_id = filter_input(INPUT_GET, "tipo_id", FILTER_VALIDATE_INT);
                        if ($tipo_id > 0) {
                            $object = $object->getById($tipo_id);
                        }
                    }
                    $viewer = ViewFactory::instance()->entryCategoryView($object);
                    print $viewer->printForm();
                    ?>

                    <tr>
                        <td><input type="submit" name="update" value="Gravar"></td>
                        <?php
                        if ($object->id != 0) {
                            ?>
                            <td><input type="submit" name="update" value="Apagar"></td>
                            <?php
                        }
                        ?>
                    </tr>
                </table>
            </form>
        </div>
        <?php Html::footer(); ?>
    </div>
</body>

</html>
