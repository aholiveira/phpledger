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
    'id' => FILTER_SANITIZE_NUMBER_INT,
    'account_id' => FILTER_SANITIZE_NUMBER_INT,
    'category_id' => FILTER_SANITIZE_NUMBER_INT,
    'currency_amount' => array(
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ),
    'currency_id' => FILTER_SANITIZE_ENCODED,
    'direction' => FILTER_SANITIZE_NUMBER_INT,
    'currency_amount' => array(
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ),
    'remarks' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'filter_entry_type' => FILTER_SANITIZE_NUMBER_INT,
    'filter_account_id' => FILTER_SANITIZE_NUMBER_INT,
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

    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    if (checkDateParameter('data_mov', $filtered_input) instanceof \DateTime) {
        $entry->entry_date = checkDateParameter('data_mov', $filtered_input)->format("Y-m-d");
    } else {
        Html::myalert("Data invalida!");
    }
    $entry->id = checkParameterExists("id", "Dados invalidos");
    $entry->currency_amount = checkParameterExists("currency_amount", "Valor movimento invalido!");
    $entry->direction = checkParameterExists("direction", "Valor movimento invalido!");
    $entry->euro_amount = $filtered_input["direction"] * $filtered_input["currency_amount"];
    $entry->category_id = checkParameterExists("category_id", "Tipo movimento invalido!");
    $entry->currency_id = checkParameterExists("currency_id", "Moeda invalida!");
    $entry->account_id = checkParameterExists("account_id", "Conta movimento invalida!");
    $entry->remarks = checkParameterExists("remarks", "Observacoes movimento invalidas!");
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
            $sdate = (strlen($filtered_input["filter_sdate"]) ? $filtered_input["filter_sdate"] : date("Y-m-01"));
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
        $ledger_filter[] = array("entry_date" => array("operator" => '>=', "value" => $sdate));
        $ledger_filter[] = array("entry_date" => array("operator" => '<=', "value" => $edate));
        if (is_array($filtered_input) && $filtered_input["filter_account_id"] > 0) {
            $ledger_filter[] = array('account_id' => array("operator" => '=', "value" => $filtered_input["filter_account_id"]));
        }
        if (is_array($filtered_input) && $filtered_input["filter_entry_type"] > 0) {
            $ledger_filter[] = array('category_id' => array("operator" => '=', "value" => $filtered_input["filter_entry_type"]));
        }
        $filter = "movimentos.entry_date>='{$sdate}' AND movimentos.entry_date<='{$edate}'";
        $parent_filter = "";
        if (is_array($filtered_input) && strlen($filtered_input["filter_parent_id"]) > 0) {
            $parent_filter = "tipo_mov.parent_id={$filtered_input['filter_parent_id']} ";
            $ledger_filter[] = array("parent_id" => array("operator" => "IN", "value" => "({$filtered_input['filter_parent_id']})"));
        }
        $edit = 0;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && is_array($filtered_input) && !empty($filtered_input["id"])) {
            $edit = $filtered_input["id"];
        }

        global $object_factory;
        // Saldo anterior
        $ledger_entry = $object_factory->ledgerentry();
        $balance = $ledger_entry->getBalanceBeforeDate($sdate, is_array($filtered_input) && $filtered_input["filter_account_id"] > 0 ? $filtered_input["filter_account_id"] : null);
        $ledger_entry_cache = ledgerentry::getList($ledger_filter);
        $entry_filter_array = array();
        if ($edit > 0) {
            $edit_entry = ledgerentry::getById($edit);
            if ($edit_entry->id != $edit) {
                die("Record not found");
            }
            $ledger_entry = ledgerentry::getById($edit);
            if ($ledger_entry->id != $edit) {
                Html::myalert("Registo com ID {$edit} nao encontrado");
            }
        }

        // Defaults
        $defaults = defaults::getById(1);
        if ($defaults->id != 1) {
            $defaults->init();
        }
        // Tipos movimento
        $category_id = $edit > 0 ? $edit_entry->category_id : $defaults->category_id;
        $entry_viewer = $view_factory->entry_category_view(entry_category::getById($category_id));
        $tipo_mov_opt = $entry_viewer->getSelectFromList(entry_category::getList(array(
            'active' => array('operator' => '=', 'value' => '1'),
            'tipo_id' => array('operator' => '>', 'value' => '0')
        )));
        // Moedas
        $currency_id = $edit > 0 ? $edit_entry->currency_id :  $defaults->currency_id;
        $currency = $object_factory->currency();
        $currency_viewer = $view_factory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList(currency::getList(), $currency_id);
        // Contas
        $conta_opt = "";
        $account_id = $edit > 0 ? $edit_entry->account_id : $defaults->account_id;
        $account_viewer = $view_factory->account_view(account::getById($account_id));
        $conta_opt = $account_viewer->getSelectFromList(account::getList(array('activa' => array('operator' => '=', 'value' => '1'))), $account_id);
        $filter_string = "";
        $filter_properties = array("filter_parent_id", "filter_entry_type", "filter_sdate", "filter_sdateAA", "filter_sdateMM", "filter_sdateDD", "filter_edate", "filter_edateAA", "filter_edateMM", "filter_edateDD", "filter_account_id");
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
                        <td><label for="filter_account_id">Conta</label> </td>
                        <td>
                            <select name="filter_account_id" id="filter_account_id" data-placeholder="Seleccione a conta" data-max="2" data-search="false" data-select-all="true" data-list-all="true" data-width="300px" data-height="50px" data-multi-select>
                                <option value>Sem filtro</option>
                                <?php print $conta_opt; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_entry_type">Categoria</label></td>
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
                document.getElementById("filter_account_id").value = "<?php print is_array($filtered_input) ? $filtered_input["filter_account_id"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="ledger_entries.php" method="POST">
                <input type="hidden" name="filter_account_id" value="<?php print is_array($filtered_input) ? $filtered_input["filter_account_id"] : ""; ?>">
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

                        foreach ($ledger_entry_cache as $row):
                            print "<tr id='{$row->id}'>";
                            $balance += $row->euro_amount;
                            if ($row->id == $edit) {
                        ?>
                                <td data-label=""><input type="hidden" name="id" value="<?php print $row->id; ?>"><input class="submit" type="submit" name="Gravar" value="Gravar"></td>
                                <td data-label="Data" class="id">
                                    <select class="date-fallback" style="display: none" name="data_movAA"><?php Html::year_option(substr($row->entry_date, 0, 4)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movMM"><?php Html::mon_option(substr($row->entry_date, 5, 2)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movDD"><?php Html::day_option(substr($row->entry_date, 8, 2)); ?></select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?php print $row->entry_date; ?>">
                                </td>
                                <td data-label="Categoria" class="category"><select name="category_id"><?php print $tipo_mov_opt; ?></select></td>
                                <td data-label="Moeda" class="currency"><select name="currency_id"><?php print $moeda_opt; ?></select></td>
                                <td data-label="Conta" class="account"><select name="account_id"><?php print $conta_opt; ?></select></td>
                                <td data-label="D/C" class="direction">
                                    <select name="direction">
                                        <option value="1" <?php print($row->direction == "1" ? " selected " : "") ?>>Dep</option>
                                        <option value="-1" <?php print($row->direction == "-1" ? " selected " : "") ?>>Lev</option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount"><input type="number" step="0.01" name="currency_amount" placeholder="0.00" value="<?php print $row->currency_amount; ?>"></td>
                                <td data-label="Obs" class="remarks"><input type="text" name="remarks" maxlength="255" value="<?php print $row->remarks; ?>"></td>
                                <td data-label="Saldo" class="total" style="text-align: right"><?php print normalize_number($balance); ?></td>
                            <?php
                            }
                            if (empty($edit) || $row->id != $edit) {
                                $category_filter = (stripos($filter_string, "filter_entry_type") === false ? "$filter_string&filter_entry_type={$row->category_id}" : preg_replace("/filter_entry_type=(\d+)/", "filter_entry_type=" . $row->category_id, $filter_string));
                                $account_filter = (stripos($filter_string, "filter_account_id") === false ? "$filter_string&filter_account_id={$row->account_id}" : preg_replace("/filter_account_id=(\d+)/", "filter_account_id=" . $row->account_id, $filter_string));
                            ?>
                                <td data-label='ID' class='id'><a title="Editar entrada" href="ledger_entries.php?<?php print "{$filter_string}&amp;id={$row->id}"; ?>#<?php print $row->id; ?>"><?php print $row->id; ?></a></td>
                                <td data-label='Data' class='data'><?php print $row->entry_date; ?></td>
                                <td data-label='Categoria' class='category'><a title="Filtrar lista para esta categoria" href="ledger_entries.php?<?php print $category_filter; ?>"><?php print ($row->category->parent_id > 0 ? $row->category->parent_description . "&#8594;" : "") . $row->category->description; ?></a></td>
                                <td data-label='Moeda' class='currency'><?php print $row->currency->description; ?></td>
                                <td data-label='Conta' class='account'><a title="Filtrar lista para esta conta" href="ledger_entries.php?<?php print $account_filter ?>"><?php print $row->account->name; ?></a></td>
                                <td data-label='D/C' class='direction'><?php print($row->direction == "1" ? "Dep" : "Lev"); ?></td>
                                <td data-label='Valor' class='amount'><?php print normalize_number($row->currency_amount); ?></td>
                                <td data-label='Obs' class='remarks'><?php print $row->remarks; ?></td>
                                <td data-label='Saldo' class='total'><?php print normalize_number($balance); ?></td>
                        <?php
                            }

                            print "</tr>\n";
                        endforeach;
                        ?>
                    </tbody>
                    <?php
                    if ($edit == 0) {
                    ?>
                        <tfoot>
                            <tr id="last">
                                <td data-label="" class="id"><input type="hidden" name="id" value="NULL"><input class="submit" type="submit" name="Gravar" value="Gravar"></td>
                                <td data-label="Data" class="data">
                                    <select class="date-fallback" style="display: none" name="data_movAA"><?php print Html::year_option(substr($defaults->entry_date, 0, 4)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movMM"><?php print Html::mon_option(substr($defaults->entry_date, 5, 2)); ?></select>
                                    <select class="date-fallback" style="display: none" name="data_movDD"><?php print Html::day_option(substr($defaults->entry_date, 8, 2)) ?></select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?php print $defaults->entry_date; ?>">
                                </td>
                                <td data-label="Categoria" class="category">
                                    <select name="category_id">
                                        <?php print $tipo_mov_opt; ?>
                                    </select>
                                </td>
                                <td data-label="Moeda" class="currency">
                                    <select name="currency_id">
                                        <?php print $moeda_opt; ?>
                                    </select>
                                </td>
                                <td data-label="Conta" class="account">
                                    <select name="account_id">
                                        <?php print $conta_opt; ?>
                                    </select>
                                </td>
                                <td data-label="D/C" class="direction">
                                    <select name="direction">
                                        <option value="1">Dep</option>
                                        <option value="-1" selected>Lev</option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount"><input type="number" step="0.01" name="currency_amount" placeholder="0.00" value="0.00"></td>
                                <td data-label="Obs" class="remarks"><input type="text" name="remarks" maxlength="255" value=""></td>
                                <td data-label="Saldo" class="total"><?php print normalize_number($balance); ?></td>
                            </tr>
                        </tfoot>
                    <?php
                    }
                    ?>
                </table>
            </form>
        </div>
        <div class="main-footer">
            <p>Transac&ccedil;&otilde;es no per&iacute;odo: <?php print sizeof($ledger_entry_cache); ?></p>
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