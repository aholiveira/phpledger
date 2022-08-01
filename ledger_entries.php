<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include ROOT_DIR . "/contas_config.php";
$pagetitle = "Movimentos";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    build_and_save_record();
}

function build_and_save_record()
{
    global $object_factory;
    $entry = $object_factory->ledgerentry();
    $defaults = $object_factory->defaults();
    $entry_date = "";
    if (strlen($_POST["data_mov"]) > 0) {
        $datetime = date_create($_POST["data_mov"]);
    } else {
        if (checkdate($_POST["data_movMM"], $_POST["data_movDD"], $_POST["data_movAA"])) {
            $datetime = date_create("{$_POST["data_movAA"]}-{$_POST["data_movMM"]}-{$_POST["data_movDD"]}");
        }
    }
    if ($datetime) {
        $entry_date = $datetime->format("Y-m-d");
    }
    if (strlen($entry_date) == 0) {
        Html::myalert("Data invalida!");
    }
    if ($_POST["mov_id"] != "NULL") {
        $entry->id = $_POST["mov_id"];
    }
    if (!is_numeric($_POST["valor_mov"]) || !is_numeric($_POST["deb_cred"])) {
        Html::myalert("Valores invalidos!");
    }
    $entry->entry_date = $entry_date;
    $entry->category_id = $_POST["tipo_mov"];
    $entry->currency_id = $_POST["moeda_mov"];
    $entry->account_id = $_POST["conta_id"];
    $entry->direction = $_POST["deb_cred"];
    $entry->currency_amount = $_POST["valor_mov"];
    $entry->euro_amount = $_POST["deb_cred"] * $_POST["valor_mov"];
    $entry->remarks = (strlen($_POST["obs"]) ? $_POST["obs"] : "");
    $entry->username = (strlen($_SESSION["user"]) ? $_SESSION["user"] : "");
    if (!$entry->save()) {
        Html::myalert("Ocorreu um erro na gravacao");
    } else {
        Html::myalert("Registo gravado. ID: " . $entry->id);
        $defaults->getById(1);
        $defaults->category_id = $entry->category_id;
        $defaults->currency_id = $entry->currency_id;
        $defaults->account_id = $entry->account_id;
        $defaults->entry_date = $entry_date;
        $defaults->direction = $entry->direction;
        $defaults->save();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include "header.php"; ?>
</head>
<script>
    function update_date(id) {
        document.getElementById(id).value =
            document.getElementById(id + 'AA').value +
            document.getElementById(id + 'MM').value +
            document.getElementById(id + 'DD').value;
    }

    function clear_filter() {
        document.getElementById("tipo_mov_filter").value = "";
        document.getElementById("conta_id_filter").value = "";
        document.getElementById("sdate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-01";
        document.getElementById("edate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-" + (new Date).getDate().toString().padStart(2, "0");
    }
</script>

<body>
    <div class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        if (array_key_exists("sdate", $_REQUEST)) {
            $sdate = (strlen($_REQUEST["sdate"]) ? str_replace("-", "", $_REQUEST["sdate"]) : date("Ym01"));
        } else {
            if (array_key_exists("sdateAA", $_REQUEST)) {
                $sdate = sprintf("%04d%02d%02d", $_REQUEST["sdateAA"], $_REQUEST["sdateMM"], $_GET["sdateDD"]);
            } else {
                $sdate = date("Ym01");
            }
        }
        if (array_key_exists("edate", $_REQUEST)) {
            $edate = strlen($_REQUEST["edate"]) ? str_replace("-", "", $_REQUEST["edate"]) : date("Ymd");
        } else {
            if (array_key_exists("edateAA", $_REQUEST)) {
                $edate = sprintf("%04d%02d%02d", $_REQUEST["edateAA"], $_REQUEST["edateMM"], $_REQUEST["edateDD"]);
            } else {
                $edate = date("Ymd");
            }
        }
        $filter = "movimentos.data_mov>='" . $sdate . "' AND movimentos.data_mov<='" . $edate . "'";
        $parent_filter = "";
        if (array_key_exists("parent_id", $_REQUEST) && strlen($_REQUEST["parent_id"]) > 0) {
            $parent_filter = "tipo_mov.parent_id={$_REQUEST["parent_id"]} ";
        }

        $sql = "SELECT mov_id, data_mov, tipo_mov, CONCAT(IF(tipo_mov.parent_id=0,'', CONCAT(parent.tipo_desc,'&#8594;')), tipo_mov.tipo_desc) as tipo_desc, movimentos.conta_id, conta_nome, round(valor_mov,2) as val_mov, deb_cred, moeda_mov, moeda_desc, cambio, valor_euro, obs " .
            "FROM movimentos 
            RIGHT JOIN tipo_mov ON movimentos.tipo_mov = tipo_mov.tipo_id 
            RIGHT JOIN tipo_mov as parent ON tipo_mov.parent_id = parent.tipo_id
            RIGHT JOIN moedas ON movimentos.moeda_mov = moedas.moeda_id 
            RIGHT JOIN contas ON movimentos.conta_id = contas.conta_id WHERE " .
            (array_key_exists("conta_id_filter", $_REQUEST) && strlen($_REQUEST["conta_id_filter"]) > 0 ? " movimentos.conta_id=\"" . $_REQUEST["conta_id_filter"] . "\" AND " : "") . $filter .
            (array_key_exists("tipo_mov_filter", $_REQUEST) && strlen($_REQUEST["tipo_mov_filter"]) > 0 ? " AND (movimentos.tipo_mov={$_REQUEST["tipo_mov_filter"]}" . (strlen($parent_filter) > 0 ? " OR {$parent_filter})" : ")") : "") .
            " ORDER BY data_mov, mov_id";
        if (array_key_exists("mov_id", $_GET)) {
            $aux_sql = "SELECT mov_id, tipo_mov, moeda_mov, conta_id " .
                "FROM movimentos " .
                "WHERE mov_id=" . $_GET["mov_id"];
            $aux_res = $db_link->query($aux_sql);
            if (mysqli_num_rows($aux_res) != 1)
                die("Record not found");
            $aux_row = $aux_res->fetch_assoc();
            $aux_res->close();
        } else {
            $aux_sql = "";
            $aux_row = NULL;
        }

        // Saldo anterior
        global $object_factory;
        $ledger_entry = $object_factory->ledgerentry();
        $saldo = $ledger_entry->getBalanceBeforeDate($sdate, array_key_exists("conta_id_filter", $_REQUEST) && strlen($_REQUEST["conta_id_filter"]) > 0 ? $_REQUEST["conta_id_filter"] : null);

        // Movimento para editar
        if (array_key_exists("mov_id", $_GET)) {
            $ledger_entry->getById($_GET["mov_id"]);
            if ($ledger_entry->id != $_GET["mov_id"]) {
                die("Record not found");
            }
        }

        // Defaults
        $defaults = $object_factory->defaults();
        $defaults->getById(1);
        if ($defaults->id != 1) {
            $defaults->init();
        }
        // Tipos movimento
        $category_id = 0;
        if (is_array($aux_row) && array_key_exists("tipo_mov", $aux_row)) {
            $category_id = $aux_row["tipo_mov"];
        } else {
            $category_id = $defaults->category_id;
        }
        $entry_category = $object_factory->entry_category();
        $entry_category->getById($category_id);
        $entry_viewer = $view_factory->entry_category_view($entry_category);
        $tipo_mov_opt = $entry_viewer->getSelectFromList($entry_category->getAll(array(
            'active' => array('operator' => '=', 'value' => '1'),
            'tipo_id' => array('operator' => '>', 'value' => '0')
        )));
        // Moedas
        $currency_id = 0;
        if (is_array($aux_row) && array_key_exists("moeda_mov", $aux_row)) {
            $currency_id = $aux_row["moeda_mov"];
        } else {
            $currency_id = $defaults->currency_id;
        }
        $currency = $object_factory->currency();
        $currency_viewer = $view_factory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList($currency->getAll(), $currency_id);

        // Contas
        $conta_opt = "";
        $account_id = 0;
        if (is_array($aux_row) && array_key_exists("conta_id", $aux_row)) {
            $account_id = $aux_row["conta_id"];
        } else {
            $account_id = $defaults->account_id;
        }
        $account = $object_factory->account();
        $account->getById($account_id);
        $account_viewer = $view_factory->account_view($account);
        $conta_opt = $account_viewer->getSelectFromList($account->getAll(array('activa' => array('operator' => '=', 'value' => '1'))), $account_id);
        ?>
        <div class="header" id="header">
            <form name="datefilter" action="ledger_entries.php" method="GET">
                <input type="hidden" name="parent_id" value="<?php print array_key_exists("parent_id", $_REQUEST) ? $_REQUEST["conta_id"] : ""; ?>" />
                <input type="hidden" name="tipo_mov_filter" value="<?php print array_key_exists("tipo_mov_filter", $_REQUEST) ? $_REQUEST["tipo_mov_filter"] : ""; ?>" />
                <table class="filter">
                    <tr>
                        <td>Inicio</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="sdateAA" onchange="update_date('sdate');"><?php print Html::year_option(substr($sdate, 0, 4)); ?></select>
                            <select class="date-fallback" style="display: none" name="sdateMM" onchange="update_date('sdate');"><?php print Html::mon_option(substr($sdate, 4, 2)); ?></select>
                            <select class="date-fallback" style="display: none" name="sdateDD" onchange="update_date('sdate');"><?php print Html::day_option(substr($sdate, 6, 2)); ?></select>
                            <input class="date-fallback" type="date" id="sdate" name="sdate" required value="<?php print (new DateTime("{$sdate}"))->format("Y-m-d"); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Fim</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="edateAA" onchange="update_date('edate');"><?php print Html::year_option(substr($edate, 0, 4)); ?></select>
                            <select class="date-fallback" style="display: none" name="edateMM" onchange="update_date('edate');"><?php print Html::mon_option(substr($edate, 4, 2)); ?></select>
                            <select class="date-fallback" style="display: none" name="edateDD" onchange="update_date('edate');"><?php print Html::day_option(substr($edate, 6, 2)); ?></select>
                            <input class="date-fallback" type="date" id="edate" name="edate" required value="<?php print (new DateTime("{$edate}"))->format("Y-m-d"); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Conta</td>
                        <td>
                            <select name="conta_id_filter" id="conta_id_filter" placeholder="Escolha uma conta">
                                <option value=""></option>
                                <?php print $conta_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Categoria</td>
                        <td>
                            <select name="tipo_mov_filter" id="tipo_mov_filter" placeholder="Escolha uma categoria">
                                <option value=""></option>
                                <?php print $tipo_mov_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input class="submit" type="submit" value="Filtrar" />
                            <input class="submit" type="button" value="Limpar filtro" onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();" />
                        </td>
                    </tr>
                </table>
            </form>
            <script>
                document.getElementById("tipo_mov_filter").value = "<?php print array_key_exists("tipo_mov_filter", $_REQUEST) ? $_REQUEST["tipo_mov_filter"] : ""; ?>";
                document.getElementById("conta_id_filter").value = "<?php print array_key_exists("conta_id_filter", $_REQUEST) ? $_REQUEST["conta_id_filter"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="ledger_entries.php" method="POST">
                <input type="hidden" name="conta_id_filter" value="<?php print array_key_exists("conta_id_filter", $_REQUEST) ? $_REQUEST["conta_id_filter"] : ""; ?>" />
                <input type="hidden" name="parent_id" value="<?php print array_key_exists("parent_id", $_REQUEST) ? $_REQUEST["parent_id"] : ""; ?>" />
                <input type="hidden" name="tipo_mov_filter" value="<?php print array_key_exists("tipo_mov_filter", $_REQUEST) ? $_REQUEST["tipo_mov_filter"] : ""; ?>" />
                <input type="hidden" name="sdate" value="<?php print $sdate; ?>" />
                <input type="hidden" name="edate" value="<?php print $edate; ?>" />
                <table class="lista ledger_entry_list">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Data</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Moeda</th>
                            <th scope="col">Conta</th>
                            <th scope="col">D/C</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Obs</th>
                            <th scope="col">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="balance-label" colspan="8">Saldo anterior</td>
                            <td data-label="Saldo anterior" class="balance"><?php print normalize_number($saldo); ?></td>
                        </tr>
                        <?php
                        $result = $db_link->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            print "<tr>";
                            $saldo = $saldo + $row["valor_euro"];
                            if (array_key_exists("mov_id", $_GET)) {
                                if ($row["mov_id"] == $_GET["mov_id"]) {
                                    print "<td data-label=''><input type=\"hidden\" readonly size=\"4\" name=\"mov_id\" value=\"{$row["mov_id"]}\" /><input class=\"submit\" type=\"submit\" name=\"Gravar\" value=\"Gravar\" /></td>\n";
                                    print "<td data-label='Data' class='id'><a name=\"{$row["mov_id"]}\">";
                                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movAA\">" . Html::year_option(substr($row["data_mov"], 0, 4)) . "</select>";
                                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movMM\">" . Html::mon_option(substr($row["data_mov"], 5, 2)) . "</select>";
                                    print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movDD\">" . Html::day_option(substr($row["data_mov"], 8, 2)) . "</select>";
                                    print "<input class=\"date-fallback\" type=\"date\" name=\"data_mov\" required value=\"{$row["data_mov"]}\">";
                                    print "</a></td>\n";
                                    print "<td data-label='Categoria' class='category'><select name=\"tipo_mov\">{$tipo_mov_opt}</select></td>\n";
                                    print "<td data-label='Moeda' class='currency'><select name=\"moeda_mov\">{$moeda_opt}</select></td>\n";
                                    print "<td data-label='Conta' class='account'><select name=\"conta_id\">{$conta_opt}</select></td>\n";
                                    print "<td data-label='D/C' class='direction'><select name=\"deb_cred\"><option value=\"1\"" . ($row["deb_cred"] == "1" ? " selected " : "") . ">Dep</option><option value=\"-1\"" . ($row["deb_cred"] == "-1" ? " selected " : "") . ">Lev</option></select></td>\n";
                                    print "<td data-label='Valor' class='amount'><input style=\"text-align: right\" type=text name=\" valor_mov\" maxlength=8 value=\"{$row["val_mov"]}\" />\n";
                                    print "<td data-label='Obs' class='remarks'><input type=\"text\" name=\"obs\" maxlength=\"255\" value=\"{$row["obs"]}\" /></td>\n";
                                    print "<td data-label='Saldo' class='total' style=\"text-align: right\">" . normalize_number($saldo) . "</td>\n";
                                }
                            }
                            if (!array_key_exists("mov_id", $_GET) || $row["mov_id"] != $_GET["mov_id"]) {
                                print "<td data-label='ID' class='id'><a name=\"{$row["mov_id"]}\" title=\"Editar entrada\" href=\"ledger_entries.php?sdate={$sdate}&amp;edate={$edate}&amp;mov_id={$row["mov_id"]}" . (array_key_exists("conta_id_filter", $_GET) ? "&amp;conta_id_filter={$_GET["conta_id_filter"]}" : "") . (array_key_exists("tipo_mov", $_GET) ? "&amp;tipo_mov_filter={$_GET["tipo_mov_filter"]}" : "") . "#\">{$row["mov_id"]}</a></td>\n";
                                print "<td data-label='Data' class='data'>{$row["data_mov"]}</td>\n";
                                print "<td data-label='Categoria' class='category'><a title=\"Filtrar lista para esta categoria\" href=\"ledger_entries.php?sdate={$sdate}&amp;edate={$edate}&amp;tipo_mov_filter={$row["tipo_mov"]}" . (array_key_exists("conta_id_filter", $_GET) ? "&amp;conta_id_filter={$_GET["conta_id_filter"]}" : "") . "\">{$row["tipo_desc"]}</a></td>\n";
                                print "<td data-label='Moeda' class='currency'>{$row["moeda_desc"]}</td>\n";
                                print "<td data-label='Conta' class='account'><a title=\"Filtrar lista para esta conta\" href=\"ledger_entries.php?sdate={$sdate}&amp;edate={$edate}&amp;conta_id_filter={$row["conta_id"]}" . (array_key_exists("tipo_mov", $_GET) ? "&amp;tipo_mov_filter={$_GET["tipo_mov_filter"]}" : "") . "\">{$row["conta_nome"]}</a></td>\n";
                                print "<td data-label='D/C' class='direction'>" . ($row["deb_cred"] == "1" ? "Dep" : "Lev") . "</td>\n";
                                print "<td data-label='Valor' class='amount'>" . normalize_number($row["val_mov"]) . "</td>\n";
                                print "<td data-label='Obs' class='remarks'>{$row["obs"]}</td>\n";
                                print "<td data-label='Saldo' class='total'>" . normalize_number($saldo) . "</td>\n";
                            }
                            print "</tr>\n";
                        }
                        $num_rows = mysqli_num_rows($result);
                        $result->close();
                        if (!array_key_exists("mov_id", $_GET)) {
                            print "<tfoot>";
                            print "<tr>";
                            print "<td data-label='' class='id'><a name=\"last\"><input type=hidden readonly size=4 name=mov_id value=\"NULL\" /><input class=\"submit\" type=\"submit\" name=\"Gravar\" value=\"Gravar\" /></a></td>\n";
                            print "<td data-label='Data' class='data'>";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movAA\">" . Html::year_option(substr($defaults->entry_date, 0, 4)) . "</select>\n";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movMM\">" . Html::mon_option(substr($defaults->entry_date, 5, 2)) . "</select>\n";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movDD\">" . Html::day_option(substr($defaults->entry_date, 8, 2)) . "</select>\n";
                            print "<input class=\"date-fallback\" type=\"date\" name=\"data_mov\" required value=\"{$defaults->entry_date}\">\r\n";
                            print "</td>";
                            print "<td data-label='Categoria' class='category'><select name=\"tipo_mov\">{$tipo_mov_opt}</select></td>\n";
                            print "<td data-label='Moeda' class='currency'><select name=\"moeda_mov\">{$moeda_opt}</select></td>\n";
                            print "<td data-label='Conta' class='account'><select name=\"conta_id\">{$conta_opt}</select></td>\n";
                            print "<td data-label='D/C' class='direction'><select name=\"deb_cred\"><option value=\"1\">Dep</option><option value=\"-1\" selected>Lev</option></select></td>\n";
                            print "<td data-label='Valor' class='amount'><input type=\"text\" style=\"text-align: right\" name=\"valor_mov\" maxlength=\"8\" value=\"0.0\" /></td>\n";
                            print "<td data-label='Obs' class='remarks'><input type=\"text\" name=\"obs\" maxlength=\"255\" value=\"\" /></td>\n";
                            print "<td data-label='Saldo' class='total'>" . normalize_number($saldo) . "</td>\n";
                            print "</tr>\n";
                            print "</tfoot>";
                        }
                        $db_link->close();
                        print "</table>\n";
                        print "</form>\n";
                        print "</div>";
                        print "<div class=\"main-footer\">\n";
                        print "<p>Transac&ccedil;&otilde;es no per&iacute;odo: {$num_rows}</p>\n";
                        print "</div>";
                        include "footer.php";
                        ?>
        </div>
        <script>
            var test = document.createElement("input");
            try {
                test.type = "date";
                row = document.getElementsByClassName("date-fallback");
                for (i = 0; i < row.length; i++) {
                    if (row[i].style.display == "none" && row[i].tagName == "SELECT") {
                        row[i].value = "";
                    }
                }
                document.getElementsByName("data_mov").item(0).focus();
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
                document.getElementsByName("data_movAA").item(0).focus();
            }
        </script>
    </div> <!-- Main grid -->
</body>

</html>