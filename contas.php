<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include ROOT_DIR . "/contas_config.php";
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
        if (array_key_exists("update", $_POST) && strcasecmp($_POST["update"], "gravar") == 0) {
            if (!strlen($_POST["conta_nome"])) {
                Html::myalert("Nome de conta invalido!");
            }
            if (strlen($_POST["abertura"]) == 0) {
                if (!checkdate($_POST["aberturaMM"], $_POST["aberturaDD"], $_POST["aberturaAA"])) {
                    Html::myalert("Data de abertura invalida!");
                } else {
                    $open_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $_POST["aberturaMM"], $_POST["aberturaDD"], $_POST["aberturaAA"])));
                }
            } else {
                $open_date = new DateTime($_POST["abertura"]);
                if (!checkdate($open_date->format("m"), $open_date->format("d"), $open_date->format("Y"))) {
                    Html::myalert("Data de abertura invalida!");
                }
            }
            if (strlen($_POST["fecho"]) == 0) {
                if (!checkdate($_POST["fechoMM"], $_POST["fechoDD"], $_POST["fechoAA"])) {
                    Html::myalert("Data de fecho invalida!");
                } else {
                    $close_date = new DateTime(date("Y-m-d", mktime(0, 0, 0, $_POST["fechoMM"], $_POST["fechoDD"], $_POST["fechoAA"])));
                }
            } else {
                $close_date = new DateTime($_POST["fecho"]);
                if (!checkdate($close_date->format("m"), $close_date->format("d"), $close_date->format("Y"))) {
                    Html::myalert("Data de fecho invalida!");
                }
            }
            $object->id = $_POST["conta_id"];
            $object->name = $_POST["conta_nome"];
            $object->open_date = $open_date->format("Y-m-d");
            $object->close_date = $close_date->format("Y-m-d");
            $object->number = $_POST["conta_num"] == "" ? "" : $_POST["conta_num"];
            $object->active = $_POST["activa"] == "on" ? 1 : 0;
            $object->type_id = $_POST["tipo_id"];
            $object->iban = $_POST["conta_nib"];
            $retval = $object->save();
        }
        if (array_key_exists("update", $_REQUEST) && strcasecmp($_REQUEST["update"], "apagar") == 0) {
            $object->id = $_REQUEST["conta_id"];
            $retval = $object->delete();
        }
        if (array_key_exists("update", $_REQUEST)) {
            if ($retval) {
                if (strcasecmp($_REQUEST["update"], "gravar") == 0) {
                    Html::myalert("Registo gravado");
                }
                if (strcasecmp($_REQUEST["update"], "apagar") == 0) {
                    Html::myalert("Registo eliminado");
                }
            } else {
                Html::myalert("Ocorreu um erro ao modificar o registo");
            }
        }
        $account_type = $object_factory->accounttype();
        $account_type_view = $view_factory->account_type_view($account_type);
        $tipo_opt = $account_type_view->getSelectFromList($account_type->getAll(), array_key_exists("conta_id", $_GET) ? $row["tipo_id"] : null);
        $sql = "SELECT conta_id, conta_num, conta_nome, contas.tipo_id, tipo_desc, conta_nib, conta_abertura, conta_fecho, activa, IF(activa,\"Sim\", \"Nao\") as activa_txt 
        FROM contas LEFT JOIN tipo_contas ON contas.tipo_id = tipo_contas.tipo_id 
        ORDER BY activa desc, conta_nome";
        $result = $db_link->query($sql) or die($db_link->error);
        ?>
        <div class="header" style="height: 0;"></div>
        <div class="main" id="main">
            <?php
            print "<form method=\"POST\" action=\"contas.php\" name=\"contas\">\n";
            print "<table class=\"lista contas\">\n";
            print "<thead><tr><th>ID<th>Nome<th>Numero<th>Tipo<th>NIB<th>Abertura<th>Fecho<th>Activa<th>Apagar</tr></thead>";
            $last = array_key_exists("conta_id", $_GET) ? 1 : 0;
            $flag = 1;
            $row = $result->fetch_assoc();
            print "<tbody>";
            while ($flag) {
                if (!$row && !$last)
                    $last = 1;
                print "<tr>";
                if ((array_key_exists("conta_id", $_GET) && $row["conta_id"] == $_GET["conta_id"]) || (!array_key_exists("conta_id", $_GET) && $last)) {
                    $id = array_key_exists("conta_id", $_GET) ? $row["conta_id"] : $object->getFreeId();
                    print "<td data-label='ID'><input type=\"hidden\" name=\"conta_id\" value=\"{$id}\"/>{$id}</td>\n";
                    print "<td data-label='Nome'><a name=\"{$id}\"><input type=text size=16 maxlength=30 name=\"conta_nome\" value=\"" . (array_key_exists("conta_id", $_GET) ? $row["conta_nome"] : "") . "\"></a></td>";
                    print "<td data-label='Numero'><input type=text size=15 maxlength=30 name=\"conta_num\" value=\"" . (array_key_exists("conta_id", $_GET) ? $row["conta_num"] : "") . "\"></td>";
                    print "<td data-label='Tipo'><select name=\"tipo_id\">{$tipo_opt}</select>";
                    print "<td data-label='NIB'><input type=text size=24 maxlength=24 name=\"conta_nib\" value=\"" . (array_key_exists("conta_id", $_GET) ? $row["conta_nib"] : "") . "\"></td>";
                    print "<td data-label='Abertura'>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaAA\">" . Html::year_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_abertura"], 0, 4) : null) . "</select>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaMM\">" . Html::mon_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_abertura"], 5, 2) : null) . "</select>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaDD\">" . Html::day_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_abertura"], 8, 2) : null) . "</select>\r\n";
                    print "<input class=\"date-fallback\" type=\"date\" name=\"abertura\" required value=\"" . (array_key_exists("conta_id", $_GET) ? $row["conta_abertura"] : date("Y-m-d")) . "\">\r\n";
                    print "</td>\r\n";
                    print "<td data-label='Fecho'>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoAA\">" . Html::year_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_fecho"], 0, 4) : null) . "</select>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoMM\">" . Html::mon_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_fecho"], 5, 2) : null) . "</select>\r\n";
                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoDD\">" . Html::day_option(array_key_exists("conta_id", $_GET) ? substr($row["conta_fecho"], 8, 2) : null) . "</select>\r\n";
                    print "<input class=\"date-fallback\" type=\"date\" name=\"fecho\" required value=\"" . (array_key_exists("conta_id", $_GET) ? $row["conta_fecho"] : date("Y-m-d")) . "\">\r\n";
                    print "</td>\r\n";
                    print "<td data-label='Activa'><input  type=\"checkbox\" name=\"activa\" " . (($row["activa"] == 1 || $last == 1) ? "checked" : "") . "></td>\r\n";
                    if ((array_key_exists("conta_id", $_REQUEST) && $_REQUEST["conta_id"] == $row["conta_id"]) || $last)
                        print "<td><input class=\"submit\" type=\"submit\" name=\"update\" value=Gravar></td>";
                    else
                        print "<td><a href=\"contas.php?update=Apagar&amp;conta_id={$row["conta_id"]}\" onclick=\"return confirm('Pretende apagar o registo?');\">Apagar</a></td>";
                } else {
                    print "<td data-label='ID'><a href=\"contas.php?conta_id={$row["conta_id"]}#\">{$row["conta_id"]}</a></td>";
                    print "<td data-label='Nome'>{$row["conta_nome"]}</td>";
                    print "<td data-label='Numero'>{$row["conta_num"]}</td>";
                    print "<td data-label='Tipo'>{$row["tipo_desc"]}</td>";
                    print "<td data-label='NIB'>{$row["conta_nib"]}</td>";
                    print "<td data-label='Abertura'>{$row["conta_abertura"]}</td>";
                    print "<td data-label='Fecho'>{$row["conta_fecho"]}</td>";
                    print "<td data-label='Activa' class=\"checkbox\"><input type=\"checkbox\" readonly onclick=\"return false;\" name=active{$row["conta_id"]} " . ($row["activa"] ? "checked" : "") . "></td>\n";
                    print "<td class=\"lista\"><a href=\"contas.php?update=Apagar&amp;conta_id={$row["conta_id"]}\" onclick=\"return confirm('Pretende apagar o registo?');\">Apagar</a></td>";
                }
                print "</tr>\n";
                $row = $result->fetch_assoc();
                if (!$row && $last)
                    $flag = 0;
            }
            print "</tbody>";
            $result->close();
            $db_link->close();
            ?>
            </table>
            </form>
        </div>
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