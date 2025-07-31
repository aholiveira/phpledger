<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
if (!defined("ROOT_DIR")) {
    require_once __DIR__ . "/prepend.php";
}
require_once __DIR__ . "/contas_config.php";

$pagetitle = "Movimentos";
$input_variables_filter = [
    'data_mov' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
    ],
    'data_movAA' => FILTER_SANITIZE_NUMBER_INT,
    'data_movMM' => FILTER_SANITIZE_NUMBER_INT,
    'data_movDD' => FILTER_SANITIZE_NUMBER_INT,
    'id' => FILTER_SANITIZE_NUMBER_INT,
    'account_id' => FILTER_SANITIZE_NUMBER_INT,
    'category_id' => FILTER_SANITIZE_NUMBER_INT,
    'currency_amount' => [
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ],
    'currency_id' => FILTER_SANITIZE_ENCODED,
    'direction' => FILTER_SANITIZE_NUMBER_INT,
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
];
$filtered_input = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, TRUE);
    try {
        (new LedgerEntryController($object_factory))->handleSave($filtered_input);
    } catch (\Exception $e) {
        Html::myalert($e->getMessage());
    }
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $filtered_input = filter_input_array(INPUT_GET, $input_variables_filter, TRUE);
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
        if (!empty($filtered_input["filter_sdate"])) {
            $sdate = strlen($filtered_input["filter_sdate"]) ? $filtered_input["filter_sdate"] : date("Y-m-01");
        } else {
            if (!empty($filtered_input["filter_sdateAA"])) {
                $sdate = sprintf("%04d-%02d-%02d", $filtered_input["filter_sdateAA"], $filtered_input["filter_sdateMM"], $filtered_input["filter_sdateDD"]);
            } else {
                $sdate = date("Y-m-01");
            }
        }
        if (!empty($filtered_input["filter_edate"])) {
            $edate = strlen($filtered_input["filter_edate"]) ? str_replace("-", "", $filtered_input["filter_edate"]) : date("Ymd");
        } else {
            if (is_array($filtered_input) && !empty($filtered_input["filter_edateAA"])) {
                $edate = sprintf("%04d-%02d-%02d", $filtered_input["filter_edateAA"], $filtered_input["filter_edateMM"], $filtered_input["filter_edateDD"]);
            } else {
                $edate = date("Y-m-d");
            }
        }
        $ledger_filter[] = ["entry_date" => ["operator" => '>=', "value" => $sdate]];
        $ledger_filter[] = ["entry_date" => ["operator" => '<=', "value" => $edate]];
        if (!empty($filtered_input["filter_account_id"])) {
            $ledger_filter[] = ['account_id' => ["operator" => '=', "value" => $filtered_input["filter_account_id"]]];
        }
        if (!empty($filtered_input["filter_entry_type"])) {
            $ledger_filter[] = ['category_id' => ["operator" => '=', "value" => $filtered_input["filter_entry_type"]]];
        }
        $filter = "movimentos.entry_date>='{$sdate}' AND movimentos.entry_date<='{$edate}'";
        $parent_filter = "";
        if (!empty($filtered_input["filter_parent_id"])) {
            $parent_filter = "tipo_mov.parent_id={$filtered_input['filter_parent_id']} ";
            $ledger_filter[] = ["parent_id" => ["operator" => "IN", "value" => "({$filtered_input['filter_parent_id']})"]];
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
        $entry_filter_array = [];
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
        $defaults = defaults::getByUsername($_SESSION["user"]);
        if (null === $defaults) {
            $defaults = defaults::init();
        }
        // Tipos movimento
        $category_id = $edit > 0 ? $edit_entry->category_id : $defaults->category_id;
        $entry_viewer = $view_factory->entry_category_view(entry_category::getById($category_id));
        $tipo_mov_opt = $entry_viewer->getSelectFromList(entry_category::getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'tipo_id' => ['operator' => '>', 'value' => '0']
        ]));

        // Moedas
        $currency_id = $edit > 0 ? $edit_entry->currency_id : $defaults->currency_id;
        $currency = $object_factory->currency();
        $currency_viewer = $view_factory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList(currency::getList(), $currency_id);

        // Contas
        $conta_opt = "";
        $account_id = $edit > 0 ? $edit_entry->account_id : $defaults->account_id;
        $account_viewer = $view_factory->account_view(account::getById($account_id));
        $conta_opt = $account_viewer->getSelectFromList(account::getList(['activa' => ['operator' => '=', 'value' => '1']]), $account_id);
        if (!is_array($filtered_input)) {
            $filtered_input = [];
        }
        $filtered_input2 = [];
        foreach ($filtered_input as $k => $v) {
            if (stristr($k, "filter_")) {
                $filtered_input2[$k] = $v;
            }
        }
        $filter_string = http_build_query($filtered_input2);
        ?>
        <div class="header" id="header">
            <form id="datefilter" name="datefilter" action="ledger_entries.php" method="GET">
                <input type="hidden" name="filter_parent_id"
                    value="<?= !empty($filtered_input["filter_parent_id"]) ? $filtered_input["filter_parent_id"] : "" ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filtered_input["filter_entry_type"]) ? $filtered_input["filter_entry_type"] : "" ?>">
                <table class="filter">
                    <tr>
                        <td>Inicio</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_sdateAA"
                                onchange="update_date('filter_sdate');"><?= Html::year_option(substr($sdate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateMM"
                                onchange="update_date('filter_sdate');"><?= Html::mon_option(substr($sdate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateDD"
                                onchange="update_date('filter_sdate');"><?= Html::day_option(substr($sdate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_sdate" name="filter_sdate" required
                                value="<?= (new DateTime("{$sdate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Fim</td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_edateAA"
                                onchange="update_date('filter_edate');"><?= Html::year_option(substr($edate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateMM"
                                onchange="update_date('filter_edate');"><?= Html::mon_option(substr($edate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateDD"
                                onchange="update_date('filter_edate');"><?= Html::day_option(substr($edate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_edate" name="filter_edate" required
                                value="<?= (new DateTime("{$edate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_account_id">Conta</label> </td>
                        <td>
                            <select name="filter_account_id" id="filter_account_id"
                                data-placeholder="Seleccione a conta" data-max="2" data-search="false"
                                data-select-all="true" data-list-all="true" data-width="300px" data-height="50px"
                                data-multi-select>
                                <option value>Sem filtro</option>
                                <?= $conta_opt ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_entry_type">Categoria</label></td>
                        <td>
                            <select name="filter_entry_type" id="filter_entry_type">
                                <option value>Sem filtro</option>
                                <?= $tipo_mov_opt ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input class="submit" type="submit" value="Filtrar">
                            <input class="submit" type="button" value="Limpar filtro"
                                onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();">
                        </td>
                    </tr>
                </table>
            </form>
            <script>
                document.getElementById("filter_entry_type").value = "<?= !empty($filtered_input["filter_entry_type"]) ? $filtered_input["filter_entry_type"] : ""; ?>";
                document.getElementById("filter_account_id").value = "<?= !empty($filtered_input["filter_account_id"]) ? $filtered_input["filter_account_id"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="ledger_entries.php" method="POST">
                <input type="hidden" name="filter_account_id"
                    value="<?= !empty($filtered_input["filter_account_id"]) ? $filtered_input["filter_account_id"] : ""; ?>">
                <input type="hidden" name="filter_parent_id"
                    value="<?= !empty($filtered_input["filter_parent_id"]) ? $filtered_input["filter_parent_id"] : ""; ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filtered_input["filter_entry_type"]) ? $filtered_input["filter_entry_type"] : ""; ?>">
                <input type="hidden" name="filter_sdate" value="<?= $sdate; ?>">
                <input type="hidden" name="filter_edate" value="<?= $edate; ?>">
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
                            <td data-label="Saldo anterior" class="balance"><?= normalize_number($balance); ?></td>
                        </tr>
                        <?php

                        foreach ($ledger_entry_cache as $row):
                            print "<tr id='{$row->id}'>";
                            $balance += $row->euro_amount;
                            if ($row->id == $edit) {
                                ?>
                                <td data-label=""><input type="hidden" name="id" value="<?= $row->id; ?>">
                                    <input class="submit" type="submit" name="Gravar" value="Gravar">
                                </td>
                                <td data-label="Data" class="id">
                                    <select class="date-fallback" style="display: none" name="data_movAA">
                                        <?= Html::year_option(substr($row->entry_date, 0, 4)) ?>
                                    </select>
                                    <select class="date-fallback" style="display: none" name="data_movMM">
                                        <?= Html::mon_option(substr($row->entry_date, 5, 2)) ?>
                                    </select>
                                    <select class="date-fallback" style="display: none" name="data_movDD">
                                        <?= Html::day_option(substr($row->entry_date, 8, 2)) ?>
                                    </select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                        value="<?= $row->entry_date ?>">
                                </td>
                                <td data-label="Categoria" class="category"><select
                                        name="category_id"><?= $tipo_mov_opt ?></select></td>
                                <td data-label="Moeda" class="currency"><select name="currency_id"><?= $moeda_opt ?></select>
                                </td>
                                <td data-label="Conta" class="account"><select name="account_id"><?= $conta_opt ?></select>
                                </td>
                                <td data-label="D/C" class="direction">
                                    <select name="direction">
                                        <option value="1" <?= $row->direction == "1" ? " selected " : "" ?>>Dep</option>
                                        <option value="-1" <?= $row->direction == "-1" ? " selected " : "" ?>>Lev
                                        </option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount"><input type="number" step="0.01" name="currency_amount"
                                        placeholder="0.00" value="<?= $row->currency_amount ?>"></td>
                                <td data-label="Obs" class="remarks"><input type="text" name="remarks" maxlength="255"
                                        value="<?= $row->remarks ?>"></td>
                                <td data-label="Saldo" class="total" style="text-align: right">
                                    <?= normalize_number($balance) ?>
                                </td>
                                <?php
                            }
                            if (empty($edit) || $row->id != $edit) {
                                $filtered_input3 = $filtered_input2;
                                $filtered_input3["filter_entry_type"] = $row->category_id;
                                $category_filter = http_build_query($filtered_input3);
                                $filtered_input3 = $filtered_input2;
                                $filtered_input3["filter_account_id"] = $row->account_id;
                                $account_filter = http_build_query($filtered_input3);
                                ?>
                                <td data-label='ID' class='id'><a
                                        title="Clique para editar entrada&#10;Modificado por <?= $row->username ?>&#10;em <?= $row->updated_at ?>"
                                        href="ledger_entries.php?<?= "{$filter_string}&amp;id={$row->id}" ?>#<?= $row->id ?>"><?= $row->id ?></a>
                                </td>
                                <td data-label='Data' class='data'><?= $row->entry_date ?></td>
                                <td data-label='Categoria' class='category'><a title="Filtrar lista para esta categoria"
                                        href="ledger_entries.php?<?= $category_filter ?>"><?= ($row->category->parent_id > 0 ? $row->category->parent_description . "&#8594;" : "") . $row->category->description ?></a>
                                </td>
                                <td data-label='Moeda' class='currency'><?= $row->currency->description ?></td>
                                <td data-label='Conta' class='account'><a title="Filtrar lista para esta conta"
                                        href="ledger_entries.php?<?= $account_filter ?>"><?= $row->account->name ?></a>
                                </td>
                                <td data-label='D/C' class='direction'><?= $row->direction == "1" ? "Dep" : "Lev" ?>
                                </td>
                                <td data-label='Valor' class='amount'><?= normalize_number($row->currency_amount) ?>
                                </td>
                                <td data-label='Obs' class='remarks'><?= $row->remarks; ?></td>
                                <td data-label='Saldo' class='total'><?= normalize_number($balance) ?></td>
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
                                <td data-label="" class="id"><input type="hidden" name="id" value="NULL">
                                    <input class="submit" type="submit" name="Gravar" value="Gravar">
                                </td>
                                <td data-label="Data" class="data">
                                    <select class="date-fallback" style="display: none" name="data_movAA">
                                        <?= Html::year_option(substr($defaults->entry_date, 0, 4)) ?>
                                    </select>
                                    <select class="date-fallback" style="display: none" name="data_movMM">
                                        <?= Html::mon_option(substr($defaults->entry_date, 5, 2)) ?>
                                    </select>
                                    <select class="date-fallback" style="display: none" name="data_movDD">
                                        <?= Html::day_option(substr($defaults->entry_date, 8, 2)) ?>
                                    </select>
                                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                        value="<?= $defaults->entry_date ?>">
                                </td>
                                <td data-label="Categoria" class="category">
                                    <select name="category_id"> <?= $tipo_mov_opt ?> </select>
                                </td>
                                <td data-label="Moeda" class="currency">
                                    <select name="currency_id"> <?= $moeda_opt ?> </select>
                                </td>
                                <td data-label="Conta" class="account">
                                    <select name="account_id"> <?= $conta_opt; ?> </select>
                                </td>
                                <td data-label="D/C" class="direction">
                                    <select name="direction">
                                        <option value="1">Dep</option>
                                        <option value="-1" selected>Lev</option>
                                    </select>
                                </td>
                                <td data-label="Valor" class="amount">
                                    <input type="number" step="0.01" name="currency_amount" placeholder="0.00" value="0.00">
                                </td>
                                <td data-label="Obs" class="remarks">
                                    <input type="text" name="remarks" maxlength="255" value="">
                                </td>
                                <td data-label="Saldo" class="total"><?= normalize_number($balance) ?></td>
                            </tr>
                        </tfoot>
                        <?php
                    }
                    ?>
                </table>
            </form>
        </div>
        <div class="main-footer">
            <p>Transac&ccedil;&otilde;es no per&iacute;odo: <?= sizeof($ledger_entry_cache) ?></p>
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