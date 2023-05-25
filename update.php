<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
#$_SESSION['user'] = 'update';
#include ROOT_DIR . "/contas_config.php";
config::init(__DIR__ . '/config.json');
$object_factory = new object_factory();
$view_factory = new view_factory();
$pagetitle = "Actualiza&ccedil;&atilde;o necess&aacute;ria";

?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <div class="maingrid">
        <div class="main">
            <?php
            $data_storage = $object_factory->data_storage();
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (strcasecmp($_REQUEST["action"], "actualizar") == 0) {
                    print "<p>Updating database...</p>";
                    if ($data_storage->update()) {
                        print "<p>Database successfully updated.</p>\r\n";
                        print "<p>Redirecting to homepage in 5 seconds.</p>\r\n";
                        print "<meta http-equiv='REFRESH' content='5; URL=index.php'>\r\n";
                    } else {
                        print "<p>Update failed. Check user permissions</p>\r\n";
                        print "<p>Message log:<br/>" . nl2br($data_storage->message()) . "</p>\r\n";
                    }
                }
            }
            ?>
            <p>A base de dados necessita actualiza&ccedil;&atilde;o</p>
            <?php
            $data_storage->check();
            print "<p>" . nl2br($data_storage->message()) . "</p>";
            ?>
            <form method="POST" action="update.php">
                <input class="submit" type="submit" name="action" value="Actualizar">
                <input class="submit" type="submit" name="action" value="Cancelar">
            </form>
        </div>
    </div>
</body>

</html>