<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
if (!defined("ROOT_DIR")) {
    include __DIR__ . "/prepend.php";
}
include __DIR__ . "/contas_config.php";
$pagetitle = "Movimentos";
$input_variables_filter = array(
    'data_mov' => array(
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => array('regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/')
    ),
    'data_movAA' => FILTER_SANITIZE_NUMBER_INT,
    'data_movMM' => FILTER_SANITIZE_NUMBER_INT,
    'data_movDD' => FILTER_SANITIZE_NUMBER_INT,
    'mov_id' => FILTER_SANITIZE_NUMBER_INT,
    'conta_id' => FILTER_SANITIZE_NUMBER_INT,
    'tipo_mov' => FILTER_SANITIZE_NUMBER_INT,
    'valor_mov' => array(
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ),
    'moeda_mov' => FILTER_SANITIZE_ENCODED,
    'deb_cred' => FILTER_SANITIZE_NUMBER_INT,
    'valor_mov' => array(
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ),
    'obs' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'filter_entry_type' => FILTER_SANITIZE_NUMBER_INT,
    'filter_conta_id' => FILTER_SANITIZE_NUMBER_INT,
    'filter_sdateAA' => FILTER_SANITIZE_NUMBER_INT,
    'filter_sdateMM' => FILTER_SANITIZE_NUMBER_INT,
    'filter_sdateDD' => FILTER_SANITIZE_NUMBER_INT,
    'filter_sdate' => FILTER_SANITIZE_ENCODED,
    'filter_edate' => FILTER_SANITIZE_ENCODED,
    'filter_edateAA' => FILTER_SANITIZE_NUMBER_INT,
    'filter_edateMM' => FILTER_SANITIZE_NUMBER_INT,
    'filter_edateDD' => FILTER_SANITIZE_NUMBER_INT
);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    build_and_save_record();
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $filtered_input = filter_input_array(INPUT_GET, $input_variables_filter, TRUE);
}

function checkDateParameter($parameterName, $value_array): DateTime
{
    $datetime = NULL;
    if (NULL != $value_array[$parameterName] && FALSE != $value_array[$parameterName] && strlen($value_array[$parameterName]) > 0) {
        $datetime = date_create($value_array[$parameterName]);
    } else {
        foreach (array('AA', 'MM', 'DD') as $datePart) {
            if (NULL != $value_array["{$parameterName}{$datePart}"] && FALSE != $value_array["{$parameterName}{$datePart}"]) {
                $dateParts[$datePart] = $value_array["{$parameterName}{$datePart}"];
            }
        }
        if (checkdate($dateParts['AA'], $dateParts['MM'], $dateParts['DD'])) {
            $datetime = date_create("{$dateParts['AA']}-{$dateParts['MM']}-{$dateParts['DD']}");
        }
    }
    return $datetime;
}

function checkParameterExists($parameterName, $errorMessage)
{
    global $filtered_input;
    if (!is_array($filtered_input)) {
        Html::myalert("No input detected");
        return null;
    }
    if (array_key_exists($parameterName, $filtered_input)) {
        if ($filtered_input[$parameterName] === NULL || $filtered_input[$parameterName] === FALSE) {
            Html::myalert($errorMessage);
        }
    }
    return $filtered_input[$parameterName];
}
function build_and_save_record()
{
    global $input_variables_filter;
    global $object_factory;
    $entry = $object_factory->ledgerentry();
    $defaults = $object_factory->defaults();
    $entry_date = "";

    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    if (checkDateParameter('data_mov', $filtered_input) instanceof \DateTime) {
        $entry->entry_date = checkDateParameter('data_mov', $filtered_input)->format("Y-m-d");
    } else {
        Html::myalert("Data invalida!");
    }
    $entry->id = checkParameterExists("mov_id", "Dados invalidos");
    $entry->currency_amount = checkParameterExists("valor_mov", "Valor movimento invalido!");
    $entry->direction = checkParameterExists("deb_cred", "Valor movimento invalido!");
    $entry->euro_amount = $filtered_input["deb_cred"] * $filtered_input["valor_mov"];
    $entry->category_id = checkParameterExists("tipo_mov", "Tipo movimento invalido!");
    $entry->currency_id = checkParameterExists("moeda_mov", "Moeda invalida!");
    $entry->account_id = checkParameterExists("conta_id", "Conta movimento invalida!");
    $entry->remarks = checkParameterExists("obs", "Observacoes movimento invalidas!");
    $entry->username = (strlen($_SESSION["user"]) ? $_SESSION["user"] : "");
    if (!$entry->save()) {
        Html::myalert("Ocorreu um erro na gravacao");
    } else {
        $defaults->getById(1);
        $defaults->category_id = $entry->category_id;
        $defaults->currency_id = $entry->currency_id;
        $defaults->account_id = $entry->account_id;
        $defaults->entry_date = $entry->entry_date;
        $defaults->direction = $entry->direction;
        $defaults->save();
        Html::myalert("Registo gravado [ID: {$entry->id}]");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
    <script>
        function update_date(id) {
            document.getElementById(id).value =
                document.getElementById(id + 'AA').value +
                document.getElementById(id + 'MM').value +
                document.getElementById(id + 'DD').value;
        }

        function clear_filter() {
            document.getElementById("filter_entry_type").value = "";
            document.getElementById("filter_conta_id").value = "";
            document.getElementById("filter_sdate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-01";
            document.getElementById("filter_edate").value = (new Date).getFullYear().toString() + "-" + ((new Date).getMonth() + 1).toString().padStart(2, "0") + "-" + (new Date).getDate().toString().padStart(2, "0");
            document.getElementsByName("datefilter")[0].submit();
        }

        function add_filter(filter_name, filter_value) {
            document.getElementById("filter_" + filter_name).value = filter_value;
        }
    </script>
</head>

<body>
    <div class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        if (array_key_exists("filter_sdate", $filtered_input) && !empty($filtered_input["filter_sdate"])) {
            $sdate = (strlen($filtered_input["filter_sdate"]) ? str_replace("-", "", $filtered_input["filter_sdate"]) : date("Ym01"));
        } else {
            if (array_key_exists("filter_sdateAA", $filtered_input) && !empty($filtered_input["filter_sdateAA"])) {
                $sdate = sprintf("%04d-%02d-%02d", $filtered_input["filter_sdateAA"], $filtered_input["filter_sdateMM"], $filtered_input["filter_sdateDD"]);
            } else {
                $sdate = date("Y-m-01");
            }
        }
        if (array_key_exists("filter_edate", $filtered_input) && !empty($filtered_input["filter_edate"])) {
            $edate = strlen($filtered_input["filter_edate"]) ? str_replace("-", "", $filtered_input["filter_edate"]) : date("Ymd");
        } else {
            if (array_key_exists("filter_edateAA", $filtered_input) && !empty($filtered_input["filter_edateAA"])) {
                $edate = sprintf("%04d-%02d-%02d", $filtered_input["filter_edateAA"], $filtered_input["filter_edateMM"], $filtered_input["filter_edateDD"]);
            } else {
                $edate = date("Y-m-d");
            }
        }
        $ledger_filter = array(
            'data_mov' => array('>=' => $sdate),
            'data_mov' => array('<=' => $edate)
        );
        if (array_key_exists("filter_conta_id", $filtered_input) && $filtered_input["filter_conta_id"] > 0) {
            $ledger_filter['conta_id'] = array('=' => $filtered_input["filter_conta_id"]);
        }
        if (array_key_exists("filter_entry_type", $filtered_input) && $filtered_input["filter_entry_type"] > 0) {
            $ledger_filter['tipo_mov'] = array('=' => $filtered_input["filter_entry_type"]);
        }
        $filter = "movimentos.data_mov>='" . $sdate . "' AND movimentos.data_mov<='" . $edate . "'";
        $parent_filter = "";
        if (array_key_exists("filter_parent_id", $filtered_input) && strlen($filtered_input["filter_parent_id"]) > 0) {
            $parent_filter = "tipo_mov.parent_id={$filtered_input["filter_parent_id"]} ";
        }
        $edit = 0;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && array_key_exists("mov_id", $filtered_input) && !empty($filtered_input["mov_id"])) {
            $edit = $filtered_input["mov_id"];
        }

        $sql = "SELECT mov_id, data_mov, tipo_mov,
        CONCAT(IF(tipo_mov.parent_id=0,'', CONCAT(parent.tipo_desc,'&#8594;')), tipo_mov.tipo_desc) as tipo_desc,
        movimentos.conta_id, conta_nome,
        round(valor_mov,2) as val_mov, deb_cred, moeda_mov, moeda_desc, cambio, valor_euro, obs
            FROM movimentos
            RIGHT JOIN tipo_mov ON movimentos.tipo_mov = tipo_mov.tipo_id
            RIGHT JOIN tipo_mov as parent ON tipo_mov.parent_id = parent.tipo_id
            RIGHT JOIN moedas ON movimentos.moeda_mov = moedas.moeda_id
            RIGHT JOIN contas ON movimentos.conta_id = contas.conta_id WHERE " .
            (array_key_exists("filter_conta_id", $filtered_input) && strlen($filtered_input["filter_conta_id"]) > 0 ? " movimentos.conta_id=\"" . $filtered_input["filter_conta_id"] . "\" AND " : "") . $filter .
            (array_key_exists("filter_entry_type", $filtered_input) && strlen($filtered_input["filter_entry_type"]) > 0 ? " AND (movimentos.tipo_mov={$filtered_input["filter_entry_type"]}" . (strlen($parent_filter) > 0 ? " OR {$parent_filter})" : ")") : "") .
            " ORDER BY data_mov, mov_id";
        if ($edit > 0) {
            $edit_entry = $object_factory->ledgerentry();
            $edit_entry->getById($edit);
            if ($edit_entry->id != $edit)
                die("Record not found");
        }

        // Saldo anterior
        global $object_factory;
        $ledger_entry = $object_factory->ledgerentry();
        $saldo = $ledger_entry->getBalanceBeforeDate($sdate, $filtered_input["filter_conta_id"] > 0 ? $filtered_input["filter_conta_id"] : null);

        // Movimento para editar
        if ($edit > 0) {
            $ledger_entry->getById($edit);
            if ($ledger_entry->id != $edit) {
                Html::myalert("Registo com ID {$edit} nao encontrado");
            }
        }

        // Defaults
        $defaults = $object_factory->defaults();
        $defaults->getById(1);
        if ($defaults->id != 1) {
            $defaults->init();
        }
        // Tipos movimento
        $category_id = $edit > 0 ? $edit_entry->category_id : $defaults->category_id;
        $entry_category = $object_factory->entry_category();
        $entry_category->getById($category_id);
        $entry_viewer = $view_factory->entry_category_view($entry_category);
        $tipo_mov_opt = $entry_viewer->getSelectFromList($entry_category->getAll(array(
            'active' => array('operator' => '=', 'value' => '1'),
            'tipo_id' => array('operator' => '>', 'value' => '0')
        )));
        // Moedas
        $currency_id = $edit > 0 ? $edit_entry->currency_id :  $defaults->currency_id;
        $currency = $object_factory->currency();
        $currency_viewer = $view_factory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList($currency->getAll(), $currency_id);
        // Contas
        $conta_opt = "";
        $account_id = $edit > 0 ? $edit_entry->account_id : $defaults->account_id;
        $account = $object_factory->account();
        $account->getById($account_id);
        $account_viewer = $view_factory->account_view($account);
        $conta_opt = $account_viewer->getSelectFromList($account->getAll(array('activa' => array('operator' => '=', 'value' => '1'))), $account_id);
        $filter_string = "";
        $filter_properties = array("filter_parent_id", "filter_entry_type", "filter_sdate", "filter_sdateAA", "filter_sdateMM", "filter_sdateDD", "filter_edate", "filter_edateAA", "filter_edateMM", "filter_edateDD", "filter_conta_id");
        foreach ($filter_properties as $filter_prop) {
            $filter_string .= (array_key_exists($filter_prop, $filtered_input) && !empty($filtered_input[$filter_prop]) ? (strlen($filter_string) > 0 ? "&" : "") . "$filter_prop={$filtered_input[$filter_prop]}" : "");
        }
        ?>
        <div class="header" id="header">
            <form name="datefilter" action="ledger_entries.php" method="GET">
                <input type="hidden" name="filter_parent_id" value="<?php print array_key_exists("filter_parent_id", $filtered_input) ? $filtered_input["filter_parent_id"] : ""; ?>">
                <input type="hidden" name="filter_entry_type" value="<?php print array_key_exists("filter_entry_type", $filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>">
                <table class="filter">
                    <tr>
                        <td>Inicio</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_sdateAA" onchange="update_date('filter_sdate');"><?php print Html::year_option(substr($sdate, 0, 4)); ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateMM" onchange="update_date('filter_sdate');"><?php print Html::mon_option(substr($sdate, 5, 2)); ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateDD" onchange="update_date('filter_sdate');"><?php print Html::day_option(substr($sdate, 8, 2)); ?></select>
                            <input class="date-fallback" type="date" id="filter_sdate" name="filter_sdate" required value="<?php print (new DateTime("{$sdate}"))->format("Y-m-d"); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Fim</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_edateAA" onchange="update_date('filter_edate');"><?php print Html::year_option(substr($edate, 0, 4)); ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateMM" onchange="update_date('filter_edate');"><?php print Html::mon_option(substr($edate, 5, 2)); ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateDD" onchange="update_date('filter_edate');"><?php print Html::day_option(substr($edate, 8, 2)); ?></select>
                            <input class="date-fallback" type="date" id="filter_edate" name="filter_edate" required value="<?php print (new DateTime("{$edate}"))->format("Y-m-d"); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Conta</td>
                        <td>
                            <select name="filter_conta_id" id="filter_conta_id">
                                <option value=""></option>
                                <?php print $conta_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Categoria</td>
                        <td>
                            <select name="filter_entry_type" id="filter_entry_type">
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
                document.getElementById("filter_entry_type").value = "<?php print array_key_exists("filter_entry_type", $filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>";
                document.getElementById("filter_conta_id").value = "<?php print array_key_exists("filter_conta_id", $filtered_input) ? $filtered_input["filter_conta_id"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="ledger_entries.php" method="POST">
                <input type="hidden" name="filter_conta_id" value="<?php print array_key_exists("filter_conta_id", $filtered_input) ? $filtered_input["filter_conta_id"] : ""; ?>" />
                <input type="hidden" name="filter_parent_id" value="<?php print array_key_exists("filter_parent_id", $filtered_input) ? $filtered_input["filter_parent_id"] : ""; ?>" />
                <input type="hidden" name="filter_entry_type" value="<?php print array_key_exists("filter_entry_type", $filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>" />
                <input type="hidden" name="filter_sdate" value="<?php print $sdate; ?>" />
                <input type="hidden" name="filter_edate" value="<?php print $edate; ?>" />
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
                            $saldo += $row["valor_euro"];
                            if ($row["mov_id"] == $edit) {
                                print "<td data-label=''><input type=\"hidden\" readonly size=\"4\" name=\"mov_id\" value=\"{$row["mov_id"]}\" /><input class=\"submit\" type=\"submit\" name=\"Gravar\" value=\"Gravar\" /></td>\n";
                                print "<td data-label='Data' class='id'><a name=\"{$row["mov_id"]}\">";
                                print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movAA\">" . Html::year_option(substr($row["data_mov"], 0, 4)) . "</select>";
                                print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movMM\">" . Html::mon_option(substr($row["data_mov"], 5, 2)) . "</select>";
                                print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movDD\">" . Html::day_option(substr($row["data_mov"], 8, 2)) . "</select>";
                                print "<input class=\"date-fallback\" type=\"date\" id=\"data_mov\" name=\"data_mov\" required value=\"{$row["data_mov"]}\">";
                                print "</a></td>\n";
                                print "<td data-label='Categoria' class='category'><select name=\"tipo_mov\">{$tipo_mov_opt}</select></td>\n";
                                print "<td data-label='Moeda' class='currency'><select name=\"moeda_mov\">{$moeda_opt}</select></td>\n";
                                print "<td data-label='Conta' class='account'><select name=\"conta_id\">{$conta_opt}</select></td>\n";
                                print "<td data-label='D/C' class='direction'><select name=\"deb_cred\"><option value=\"1\"" . ($row["deb_cred"] == "1" ? " selected " : "") . ">Dep</option><option value=\"-1\"" . ($row["deb_cred"] == "-1" ? " selected " : "") . ">Lev</option></select></td>\n";
                                print "<td data-label='Valor' class='amount'><input style=\"text-align: right\" type=text name=\" valor_mov\" maxlength=8 value=\"{$row["val_mov"]}\" />\n";
                                print "<td data-label='Obs' class='remarks'><input type=\"text\" name=\"obs\" maxlength=\"255\" value=\"{$row["obs"]}\" /></td>\n";
                                print "<td data-label='Saldo' class='total' style=\"text-align: right\">" . normalize_number($saldo) . "</td>\n";
                            }
                            if (empty($edit) || $row["mov_id"] != $edit) {
                                $category_filter = (stripos($filter_string, "filter_entry_type") === false ? "$filter_string&filter_entry_type={$row['tipo_mov']}" : preg_replace("/filter_entry_type=(\d+)/", "filter_entry_type=" . $row['tipo_mov'], $filter_string));
                                $account_filter = (stripos($filter_string, "filter_conta_id") === false ? "$filter_string&filter_conta_id={$row['conta_id']}" : preg_replace("/filter_conta_id=(\d+)/", "filter_conta_id=" . $row['conta_id'], $filter_string));
                        ?>
                                <td data-label='ID' class='id'><a name="<?php print $row["mov_id"] ?>" title="Editar entrada" href="ledger_entries.php?<?php print "{$filter_string}&amp;mov_id={$row['mov_id']}"; ?>#<?php print $row["mov_id"]; ?>"><?php print $row["mov_id"] ?></a></td>
                                <td data-label='Data' class='data'><?php print $row["data_mov"]; ?></td>
                                <td data-label='Categoria' class='category'><a title="Filtrar lista para esta categoria" href="ledger_entries.php?<?php print $category_filter; ?>"><?php print $row["tipo_desc"]; ?></a></td>
                                <td data-label='Moeda' class='currency'><?php print $row["moeda_desc"]; ?></td>
                                <td data-label='Conta' class='account'><a title="Filtrar lista para esta conta" href="ledger_entries.php?<?php print $account_filter ?>"><?php print $row["conta_nome"]; ?></a></td>
                                <td data-label='D/C' class='direction'><?php print($row["deb_cred"] == "1" ? "Dep" : "Lev"); ?></td>
                                <td data-label='Valor' class='amount'><?php print normalize_number($row["val_mov"]); ?></td>
                                <td data-label='Obs' class='remarks'><?php print $row["obs"]; ?></td>
                                <td data-label='Saldo' class='total'><?php print normalize_number($saldo); ?></td>
                        <?php
                            }
                            print "</tr>\n";
                        }
                        $num_rows = mysqli_num_rows($result);
                        $result->close();
                        if ($edit == 0) {
                            print "<tfoot>";
                            print "<tr>";
                            print "<td data-label='' class='id'><a name=\"last\"><input type=hidden readonly size=4 name=mov_id value=\"NULL\" /><input class=\"submit\" type=\"submit\" name=\"Gravar\" value=\"Gravar\" /></a></td>\n";
                            print "<td data-label='Data' class='data'>";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movAA\">" . Html::year_option(substr($defaults->entry_date, 0, 4)) . "</select>\n";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movMM\">" . Html::mon_option(substr($defaults->entry_date, 5, 2)) . "</select>\n";
                            print "<select class=\"date-fallback\" style=\"display: none\" name=\"data_movDD\">" . Html::day_option(substr($defaults->entry_date, 8, 2)) . "</select>\n";
                            print "<input class=\"date-fallback\" type=\"date\" id=\"data_mov\" name=\"data_mov\" required value=\"{$defaults->entry_date}\">\r\n";
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
    </div> <!-- Main grid -->
</body>
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
        elementId = "data_mov";
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
        elementId = "data_movAA";
    }
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            document.getElementById(elementId).focus();
            document.getElementById(elementId).scrollIntoView({
                behavior: "instant",
                block: "end",
                inline: "end"
            });
        }, 1)
    });
</script>

</html>