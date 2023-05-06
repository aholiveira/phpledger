<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Contas";

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
        $object = $object_factory->account();
        $retval = true;
        $update = filter_input(INPUT_POST, "update", FILTER_SANITIZE_ENCODED);
        if (strcasecmp($update, "gravar") == 0) {
            $account_name = filter_input(INPUT_POST, "conta_nome", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (!strlen($account_name)) {
                Html::myalert("Nome de conta invalido!");
                $retval = false;
            }
            if (strlen($_POST["abertura"]) == 0) {
                if (!checkdate($_POST["aberturaMM"], $_POST["aberturaDD"], $_POST["aberturaAA"])) {
                    Html::myalert("Data de abertura invalida!");
                    $retval = false;
                } else {
                    $open_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $_POST["aberturaMM"], $_POST["aberturaDD"], $_POST["aberturaAA"])));
                }
            } else {
                $open_date = new DateTime($_POST["abertura"]);
                if (!checkdate($open_date->format("m"), $open_date->format("d"), $open_date->format("Y"))) {
                    Html::myalert("Data de abertura invalida!");
                    $retval = false;
                }
            }
            if (strlen($_POST["fecho"]) == 0) {
                if (!checkdate($_POST["fechoMM"], $_POST["fechoDD"], $_POST["fechoAA"])) {
                    Html::myalert("Data de fecho invalida!");
                    $retval = false;
                } else {
                    $close_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $_POST["fechoMM"], $_POST["fechoDD"], $_POST["fechoAA"])));
                }
            } else {
                $close_date = new DateTime($_POST["fecho"]);
                if (!checkdate($close_date->format("m"), $close_date->format("d"), $close_date->format("Y"))) {
                    Html::myalert("Data de fecho invalida!");
                    $retval = false;
                }
            }
            if ($retval) {
                $object->id = filter_input(INPUT_POST, "conta_id", FILTER_VALIDATE_INT);
                $object->name = $account_name;
                $object->open_date = $open_date->format("Y-m-d");
                $object->close_date = $close_date->format("Y-m-d");
                $object->number = filter_input(INPUT_POST, "conta_num", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $object->active = filter_input(INPUT_POST, "activa", FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $object->type_id = filter_input(INPUT_POST, "tipo_id", FILTER_VALIDATE_INT);
                $object->iban =  filter_input(INPUT_POST, "conta_nib", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $retval = $object->update();
            }
        }
        if (strcasecmp($update, "apagar") == 0) {
            $object->id = filter_input(INPUT_GET, "conta_id", FILTER_VALIDATE_INT);
            $retval = $object->delete();
        }
        if (!empty($update)) {
            if ($retval) {
                if (strcasecmp($update, "gravar") == 0) {
                    Html::myalert("Registo gravado");
                }
                if (strcasecmp($update, "apagar") == 0) {
                    Html::myalert("Registo eliminado");
                }
            } else {
                Html::myalert("Ocorreu um erro ao criar/modificar o registo");
            }
        }
        $edit = null;
        $conta_id = filter_input(INPUT_GET, "conta_id", FILTER_SANITIZE_NUMBER_INT);
        if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($conta_id)) {
            $edit = $conta_id;
        }
        $account_type = $object_factory->accounttype();
        $account_type_view = $view_factory->account_type_view($account_type);
        $account = $object_factory->account();
        $account_list = $account->getList();
        $account_type_cache = array();

        $sql = "SELECT conta_id, conta_num, conta_nome, contas.tipo_id, tipo_desc, conta_nib, conta_abertura, conta_fecho, activa, IF(activa,\"Sim\", \"Nao\") as activa_txt 
        FROM contas LEFT OUTER JOIN tipo_contas ON contas.tipo_id = tipo_contas.tipo_id
        ORDER BY contas.activa desc, conta_nome";
        $result = $db_link->query($sql) or die($db_link->error);
        ?>
        <div class="header" style="height: 0;"></div>
        <main>
            <div class="main" id="main">
                <form method="POST" action="contas.php" name="contas">
                    <table class="lista contas">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Numero</th>
                                <th>Tipo</th>
                                <th>NIB</th>
                                <th>Abertura</th>
                                <th>Fecho</th>
                                <th>Activa</th>
                                <th>Apagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($account_list as $account) {
                                print "<tr>";
                                if (!array_key_exists($account->type_id, $account_type_cache)) {
                                    $account_type_cache[$account->type_id] = $account_type->getById($account->type_id);
                                }
                                $account_view = $view_factory->account_view($account);
                                if (!empty($edit) && $account->id == $edit) {
                                    $tipo_opt = $account_type_view->getSelectFromList($account_type->getList(), $account->type_id);
                                    print $account_view->printForm();
                                }
                                if (empty($edit) || (!empty($edit) && $account->id != $edit)) {
                                    print $account_view->printObject();
                                }
                                print "</tr>";
                            }
                            if (empty($edit)) {
                                print "<tr>";
                                $account = $object_factory->account();
                                $account_view = $view_factory->account_view($account);
                                print $account_view->printForm();
                                print "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </main>
        <script>
            var test = document.createElement("input");
            try {
                test.type = "date";
            } catch (e) {
                row = document.getElementsByClassName("date-fallback");
                for (i = 0; i < row.length; i++) {
                    if (row[i].style.display == "none") {
                        row[i].style.removeProperty("display");
                    } else {
                        if (row[i].tagName == "INPUT") {
                            row[i].value = "";
                            row[i].removeAttribute("required");
                        }
                        row[i].style.display = "none";
                    }
                }
            }
        </script>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>