<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

include ROOT_DIR . "/contas_config.php";
$pagetitle = "Actualiza&ccedil;&atilde;o necess&aacute;ria";

?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <div class="maingrid">
        <div class="main">
            <?php
            $checkdb = $object_factory->checkdb();
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (strcasecmp($_REQUEST["action"], "actualizar") == 0) {
                    $result = $checkdb->check();
                    if (!$result) {
                        print "<p>{$checkdb->message}</p>";
                        if ($checkdb->update()) {
                            print "<p>Database successfully updated.</p>\r\n";
                            print "<p>Redirecting to homepage in 5 seconds.</p>\r\n";
                            print "<meta http-equiv='REFRESH' content='1; URL=index.php'>\r\n";
                        } else {
                            print "<p>Update failed. Check user permissions</p>\r\n";
                            print "<p>Message log:<br/>{$checkdb->message}</p>\r\n";
                        }
                    }
                }
            }
            ?>
            <p>A base de dados necessita actualiza&ccedil;&atilde;o</p>
            <?php
            $checkdb->check();
            print "<p>{$checkdb->message}</p>";
            ?>
            <form method="POST" action="update.php">
                <input class="submit" type="submit" name="action" value="Actualizar" />
                <input class="submit" type="submit" name="action" value="Cancelar" />
            </form>
        </div>
    </div>
</body>

</html>