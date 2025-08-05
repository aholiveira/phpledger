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
        include constant("ROOT_DIR") . "/menu_div.php";
        $object = $object_factory->account();
        $retval = true;
        $input_variables_filter = [
            'abertura' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
            ],
            'fecho' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
            ],
            'aberturaAA' => FILTER_SANITIZE_NUMBER_INT,
            'aberturaMM' => FILTER_SANITIZE_NUMBER_INT,
            'aberturaDD' => FILTER_SANITIZE_NUMBER_INT,
            'fechoAA' => FILTER_SANITIZE_NUMBER_INT,
            'fechoMM' => FILTER_SANITIZE_NUMBER_INT,
            'fechoDD' => FILTER_SANITIZE_NUMBER_INT,
            'update' => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'options' => FILTER_NULL_ON_FAILURE
            ],
            'conta_nome' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'conta_id' => FILTER_VALIDATE_INT,
            'conta_num' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'activa' => FILTER_VALIDATE_BOOLEAN,
            'tipo_id' => FILTER_VALIDATE_INT,
            'conta_nib' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
        ];
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $filtered_input = filter_input_array(INPUT_GET, $input_variables_filter, true);
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
                http_response_code(400);
                Redirector::to('ledger_entries.php');
            }
            $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, true);
        }
        if (is_array($filtered_input) && strcasecmp($filtered_input["update"], "gravar") == 0) {
            if (empty($filtered_input["conta_nome"])) {
                Html::myalert("Nome de conta invalido!");
                $retval = false;
            }
            try {
                if (empty($filtered_input["abertura"])) {
                    $open_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $filtered_input["aberturaMM"], $filtered_input["aberturaDD"], $filtered_input["aberturaAA"])));
                } else {
                    $open_date = new DateTime($filtered_input["abertura"]);
                }
            } catch (Exception $ex) {
                Html::myalert("Data de abertura invalida!");
                $retval = false;
            }
            try {
                if (empty($filtered_input["fecho"])) {
                    $close_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $filtered_input["fechoMM"], $filtered_input["fechoDD"], $filtered_input["fechoAA"])));
                } else {
                    $close_date = new DateTime($filtered_input["fecho"]);
                }
            } catch (Exception $ex) {
                Html::myalert("Data de fecho invalida!");
                $retval = false;
            }
            if ($retval) {
                $object->id = $filtered_input["conta_id"];
                $object->name = $filtered_input["conta_nome"];
                $object->open_date = $open_date->format("Y-m-d");
                $object->close_date = $close_date->format("Y-m-d");
                $object->number = $filtered_input["conta_num"];
                $object->active = boolval($filtered_input["activa"]) ? 1 : 0;
                $object->type_id = $filtered_input["tipo_id"];
                $object->iban = $filtered_input["conta_nib"];
                $retval = $object->update();
            }
        }
        if (is_array($filtered_input) && strcasecmp($filtered_input["update"], "apagar") == 0) {
            $object->id = $filtered_input["conta_id"];
            $retval = $object->delete();
        }
        if (is_array($filtered_input) && !empty($filtered_input["update"])) {
            if ($retval) {
                if (strcasecmp($filtered_input["update"], "gravar") == 0) {
                    Html::myalert("Registo gravado");
                }
                if (strcasecmp($filtered_input["update"], "apagar") == 0) {
                    Html::myalert("Registo eliminado");
                }
            } else {
                Html::myalert("Ocorreu um erro ao criar/modificar o registo");
            }
        }
        $edit = null;
        $conta_id = is_array($filtered_input) ? $filtered_input["conta_id"] : null;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($conta_id)) {
            $edit = $conta_id;
        }
        $account = $object_factory->account();
        $account_list = $account->getList();
        ?>
        <div class="header" style="height: 0;"></div>
        <main>
            <div class="main" id="main">
                <div class="contas">
                    <form method="POST" action="accounts.php" name="contas">
                        <?= CSRF::inputField() ?>
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
                                    $account_view = $view_factory->account_view($account);
                                    if (!empty($edit) && $account->id == $edit) {
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