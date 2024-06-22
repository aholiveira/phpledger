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
    'filter_parent_id' => FILTER_SANITIZE_NUMBER_INT,
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
    if (!$entry->update()) {
        Html::myalert("Ocorreu um erro na gravacao");
    } else {
        $defaults = $defaults->getById(1);
        $defaults->category_id = $entry->category_id;
        $defaults->currency_id = $entry->currency_id;
        $defaults->account_id = $entry->account_id;
        $defaults->entry_date = $entry->entry_date;
        $defaults->direction = $entry->direction;
        $defaults->update();
        Html::myalert("Registo gravado [ID: {$entry->id}]");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
    <script src="ledger_entries.js"> </script>
</head>

<body>
    <div class="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        if (is_array($filtered_input) &&  !empty($filtered_input["filter_sdate"])) {
            $sdate = (strlen($filtered_input["filter_sdate"]) ? str_replace("-", "", $filtered_input["filter_sdate"]) : date("Ym01"));
        } else {
            if (is_array($filtered_input) && !empty($filtered_input["filter_sdateAA"])) {
                $sdate = sprintf("%04d-%02d-%02d", $filtered_input["filter_sdateAA"], $filtered_input["filter_sdateMM"], $filtered_input["filter_sdateDD"]);
            } else {
                $sdate = date("Y-m-01");
            }
        }
        if (is_array($filtered_input) && !empty($filtered_input["filter_edate"])) {
            $edate = strlen($filtered_input["filter_edate"]) ? str_replace("-", "", $filtered_input["filter_edate"]) : date("Ymd");
        } else {
            if (is_array($filtered_input) && !empty($filtered_input["filter_edateAA"])) {
                $edate = sprintf("%04d-%02d-%02d", $filtered_input["filter_edateAA"], $filtered_input["filter_edateMM"], $filtered_input["filter_edateDD"]);
            } else {
                $edate = date("Y-m-d");
            }
        }
        $ledger_filter = array(
            'data_mov' => array('>=' => $sdate),
            'data_mov' => array('<=' => $edate)
        );
        if (is_array($filtered_input) && $filtered_input["filter_conta_id"] > 0) {
            $ledger_filter['conta_id'] = array('=' => $filtered_input["filter_conta_id"]);
        }
        if (is_array($filtered_input) && $filtered_input["filter_entry_type"] > 0) {
            $ledger_filter['tipo_mov'] = array('=' => $filtered_input["filter_entry_type"]);
        }
        $filter = "movimentos.data_mov>='" . $sdate . "' AND movimentos.data_mov<='" . $edate . "'";
        $parent_filter = "";
        if (is_array($filtered_input) && strlen($filtered_input["filter_parent_id"]) > 0) {
            $parent_filter = "tipo_mov.parent_id={$filtered_input["filter_parent_id"]} ";
        }
        $edit = 0;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && is_array($filtered_input) && !empty($filtered_input["mov_id"])) {
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
            (is_array($filtered_input) && !empty($filtered_input["filter_conta_id"]) ? " movimentos.conta_id=\"" . $filtered_input["filter_conta_id"] . "\" AND " : "") . $filter .
            (is_array($filtered_input) && !empty($filtered_input["filter_entry_type"]) ? " AND (movimentos.tipo_mov={$filtered_input["filter_entry_type"]}" . (strlen($parent_filter) > 0 ? " OR {$parent_filter})" : ")") : "") .
            " ORDER BY data_mov, mov_id";
        global $object_factory;
        $entry_filter_array = array();
        if ($edit > 0) {
            $edit_entry = $object_factory->ledgerentry();
            $edit_entry = $edit_entry->getById($edit);
            if ($edit_entry->id != $edit)
                die("Record not found");
        }

        // Saldo anterior
        $ledger_entry = $object_factory->ledgerentry();
        $balance = $ledger_entry->getBalanceBeforeDate($sdate, is_array($filtered_input) && $filtered_input["filter_conta_id"] > 0 ? $filtered_input["filter_conta_id"] : null);

        // Movimento para editar
        if ($edit > 0) {
            $ledger_entry = $ledger_entry->getById($edit);
            if ($ledger_entry->id != $edit) {
                Html::myalert("Registo com ID {$edit} nao encontrado");
            }
        }

        // Defaults
        $defaults = $object_factory->defaults();
        $defaults = $defaults->getById(1);
        if ($defaults->id != 1) {
            $defaults->init();
        }
        // Tipos movimento
        $category_id = $edit > 0 ? $edit_entry->category_id : $defaults->category_id;
        $entry_category = $object_factory->entry_category();
        $entry_category = $entry_category->getById($category_id);
        $entry_viewer = $view_factory->entry_category_view($entry_category);
        $tipo_mov_opt = $entry_viewer->getSelectFromList($entry_category->getList(array(
            'active' => array('operator' => '=', 'value' => '1'),
            'tipo_id' => array('operator' => '>', 'value' => '0')
        )));
        // Moedas
        $currency_id = $edit > 0 ? $edit_entry->currency_id :  $defaults->currency_id;
        $currency = $object_factory->currency();
        $currency_viewer = $view_factory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList($currency->getList(), $currency_id);
        // Contas
        $conta_opt = "";
        $account_id = $edit > 0 ? $edit_entry->account_id : $defaults->account_id;
        $account = $object_factory->account();
        $account = $account->getById($account_id);
        $account_viewer = $view_factory->account_view($account);
        $conta_opt = $account_viewer->getSelectFromList($account->getList(array('activa' => array('operator' => '=', 'value' => '1'))), $account_id);
        $filter_string = "";
        $filter_properties = array("filter_parent_id", "filter_entry_type", "filter_sdate", "filter_sdateAA", "filter_sdateMM", "filter_sdateDD", "filter_edate", "filter_edateAA", "filter_edateMM", "filter_edateDD", "filter_conta_id");
        foreach ($filter_properties as $filter_prop) {
            $filter_string .= (is_array($filtered_input) && array_key_exists($filter_prop, $filtered_input) && !empty($filtered_input[$filter_prop]) ? (strlen($filter_string) > 0 ? "&" : "") . "$filter_prop={$filtered_input[$filter_prop]}" : "");
        }
        ?>
        <div class="header" id="header">
            <form name="datefilter" action="ledger_entries.php" method="GET">
                <input type="hidden" name="filter_parent_id" value="<?php print is_array($filtered_input) ? $filtered_input["filter_parent_id"] : ""; ?>">
                <input type="hidden" name="filter_entry_type" value="<?php print is_array($filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>">
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
                                <option value>Sem filtro</option>
                                <?php print $conta_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Categoria</td>
                        <td>
                            <select name="filter_entry_type" id="filter_entry_type">
                                <option value>Sem filtro</option>
                                <?php print $tipo_mov_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input class="submit" type="submit" value="Filtrar">
                            <input class="submit" type="button" value="Limpar filtro" onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();">
                        </td>
                    </tr>
                </table>
            </form>
            <script>
                document.getElementById("filter_entry_type").value = "<?php print is_array($filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>";
                document.getElementById("filter_conta_id").value = "<?php print is_array($filtered_input) ? $filtered_input["filter_conta_id"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="ledger_entries.php" method="POST">
                <input type="hidden" name="filter_conta_id" value="<?php print is_array($filtered_input) ? $filtered_input["filter_conta_id"] : ""; ?>">
                <input type="hidden" name="filter_parent_id" value="<?php print is_array($filtered_input) ? $filtered_input["filter_parent_id"] : ""; ?>">
                <input type="hidden" name="filter_entry_type" value="<?php print is_array($filtered_input) ? $filtered_input["filter_entry_type"] : ""; ?>">
                <input type="hidden" name="filter_sdate" value="<?php print $sdate; ?>">
                <input type="hidden" name="filter_edate" value="<?php print $edate; ?>">
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
                            <td data-label="Saldo anterior" class="balance"><?php print normalize_number($balance); ?></td>
                        </tr>
                        <?php
                        $result = $db_link->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            print "<tr id='{$row["mov_id"]}'>";
                            $balance += $row["valor_euro"];
                            if ($row["mov_id"] == $edit) {
                        ?>
                                <td data-label=""><input type="hidden" name="mov_id" value="<?php print $row["mov_id"]; ?>"><input class="submit" type="submit" name="Gravar" value="Gravar"></td>
                                <td data-label="Data" class="id">
                                    <select class="date-fallback" style="display: none" name="data_movAA"><?php Html::year_option(substr($row["data_mov"], 0, 4)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movMM"><?php Html::mon_option(substr($row["data_mov"], 5, 2)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movDD"><?php Html::day_option(substr($row["data_mov"], 8, 2)); ?></select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?php print $row["data_mov"]; ?>">
                                </td>
                                <td data-label="Categoria" class="category"><select name="tipo_mov"><?php print $tipo_mov_opt; ?></select></td>
                                <td data-label="Moeda" class="currency"><select name="moeda_mov"><?php print $moeda_opt; ?></select></td>
                                <td data-label="Conta" class="account"><select name="conta_id"><?php print $conta_opt; ?></select></td>
                                <td data-label="D/C" class="direction">
                                    <select name="deb_cred">
                                        <option value="1" <?php print($row["deb_cred"] == "1" ? " selected " : "") ?>>Dep</option>
                                        <option value="-1" <?php print($row["deb_cred"] == "-1" ? " selected " : "") ?>>Lev</option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount"><input style="text-align: right" type="text" name="valor_mov" maxlength="8" value="<?php print $row["val_mov"]; ?>"></td>
                                <td data-label="Obs" class="remarks"><input type="text" name="obs" maxlength="255" value="<?php print $row["obs"]; ?>"></td>
                                <td data-label="Saldo" class="total" style="text-align: right"><?php print normalize_number($balance); ?></td>
                            <?php
                            }
                            if (empty($edit) || $row["mov_id"] != $edit) {
                                $category_filter = (stripos($filter_string, "filter_entry_type") === false ? "$filter_string&filter_entry_type={$row['tipo_mov']}" : preg_replace("/filter_entry_type=(\d+)/", "filter_entry_type=" . $row['tipo_mov'], $filter_string));
                                $account_filter = (stripos($filter_string, "filter_conta_id") === false ? "$filter_string&filter_conta_id={$row['conta_id']}" : preg_replace("/filter_conta_id=(\d+)/", "filter_conta_id=" . $row['conta_id'], $filter_string));
                            ?>
                                <td data-label='ID' class='id'><a title="Editar entrada" href="ledger_entries.php?<?php print "{$filter_string}&amp;mov_id={$row['mov_id']}"; ?>#<?php print $row["mov_id"]; ?>"><?php print $row["mov_id"] ?></a></td>
                                <td data-label='Data' class='data'><?php print $row["data_mov"]; ?></td>
                                <td data-label='Categoria' class='category'><a title="Filtrar lista para esta categoria" href="ledger_entries.php?<?php print $category_filter; ?>"><?php print $row["tipo_desc"]; ?></a></td>
                                <td data-label='Moeda' class='currency'><?php print $row["moeda_desc"]; ?></td>
                                <td data-label='Conta' class='account'><a title="Filtrar lista para esta conta" href="ledger_entries.php?<?php print $account_filter ?>"><?php print $row["conta_nome"]; ?></a></td>
                                <td data-label='D/C' class='direction'><?php print($row["deb_cred"] == "1" ? "Dep" : "Lev"); ?></td>
                                <td data-label='Valor' class='amount'><?php print normalize_number($row["val_mov"]); ?></td>
                                <td data-label='Obs' class='remarks'><?php print $row["obs"]; ?></td>
                                <td data-label='Saldo' class='total'><?php print normalize_number($balance); ?></td>
                        <?php
                            }
                            print "</tr>\n";
                        }
                        $num_rows = mysqli_num_rows($result);
                        $result->close();
                        ?>
                    </tbody>
                    <?php
                    if ($edit == 0) {
                    ?>
                        <tfoot>
                            <tr id="last">
                                <td data-label="" class="id"><input type="hidden" name="mov_id" value="NULL"><input class="submit" type="submit" name="Gravar" value="Gravar"></td>
                                <td data-label="Data" class="data">
                                    <select class="date-fallback" style="display: none" name="data_movAA"><?php print Html::year_option(substr($defaults->entry_date, 0, 4)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movMM"><?php print Html::mon_option(substr($defaults->entry_date, 5, 2)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movDD"><?php print Html::day_option(substr($defaults->entry_date, 8, 2)) ?></select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?php print $defaults->entry_date; ?>">
                                </td>
                                <td data-label="Categoria" class="category">
                                    <select name="tipo_mov">
                                        <?php print $tipo_mov_opt; ?>
                                    </select>
                                </td>
                                <td data-label="Moeda" class="currency">
                                    <select name="moeda_mov">
                                        <?php print $moeda_opt; ?>
                                    </select>
                                </td>
                                <td data-label="Conta" class="account">
                                    <select name="conta_id">
                                        <?php print $conta_opt; ?>
                                    </select>
                                </td>
                                <td data-label="D/C" class="direction">
                                    <select name="deb_cred">
                                        <option value="1">Dep</option>
                                        <option value="-1" selected>Lev</option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount"><input type="text" style="text-align: right" name="valor_mov" maxlength="8" value="0.0"></td>
                                <td data-label="Obs" class="remarks"><input type="text" name="obs" maxlength="255" value=""></td>
                                <td data-label="Saldo" class="total"><?php print normalize_number($balance); ?></td>
                            </tr>
                        </tfoot>
                    <?php
                    }
                    $db_link->close();
                    ?>
                </table>
            </form>
        </div>
        <div class="main-footer">
            <p>Transac&ccedil;&otilde;es no per&iacute;odo: <?php print $num_rows; ?></p>
        </div>
        <?php
        include "footer.php";
        ?>
    </div> <!-- Main grid -->
    <script>
        toggleDateElements("data_mov");
    </script>
</body>

</html>