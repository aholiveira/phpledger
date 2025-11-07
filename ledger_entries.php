<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Controllers\LedgerEntryController;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;

if (!defined("ROOT_DIR")) {
    require_once __DIR__ . "/prepend.php";
}
require_once __DIR__ . "/contas_config.php";
ini_set('zlib.output_compression', 'Off');
ini_set('output_buffering', 'Off');
ini_set('implicit_flush', '1');
ob_implicit_flush(true);

$pagetitle = l10n::l("ledger_entries");
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
    'filter_edateDD' => FILTER_SANITIZE_NUMBER_INT,
    'lang' => FILTER_SANITIZE_ENCODED
];
$filtered_input = [];
$saved_id = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('ledger_entries.php');
    }
    $filtered_input = filter_input_array(INPUT_POST, $input_variables_filter, true);
    try {
        $saved_id = (new LedgerEntryController($objectFactory))->handleSave($filtered_input);
        $success = true;
    } catch (\Exception $e) {
        $error_essage = $e->getMessage();
        $success = false;
    }
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $filtered_input = filter_input_array(INPUT_GET, $input_variables_filter, true);
}

?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php include_once "header.php"; ?>
    <script src="ledger_entries.js"> </script>
</head>

<body>
    <?php if (!empty($saved_id)): ?>
        <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
            <?= $success ? l10n::l("save_success", $saved_id) : $error_essage ?>
        </div>
        <script>
            const el = document.getElementById('notification');
            setTimeout(() => {
                el.classList.add('hide');
                el.addEventListener('transitionend', () => el.remove(), { once: true });
            }, 2500);
        </script>
    <?php endif ?>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php
        include_once ROOT_DIR . "/menu_div.php";
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
            //$ledger_filter[] = ["parent_id" => ["operator" => "IN", "value" => "({$filtered_input['filter_parent_id']})"]];
        }
        $edit = 0;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && is_array($filtered_input) && !empty($filtered_input["id"])) {
            $edit = $filtered_input["id"];
        }

        global $objectFactory;
        // Saldo anterior
        $ledger_entry = $objectFactory->ledgerentry();
        $balance = $ledger_entry->getBalanceBeforeDate($sdate, is_array($filtered_input) && $filtered_input["filter_account_id"] > 0 ? $filtered_input["filter_account_id"] : null);
        $ledger_entry_cache = \ledgerentry::getList($ledger_filter);
        $entry_filter_array = [];
        if ($edit > 0) {
            $edit_entry = \ledgerentry::getById($edit);
            if ($edit_entry->id != $edit) {
                die(l10n::l('not_found', $edit));
            }
            $ledger_entry = \ledgerentry::getById($edit);
            if ($ledger_entry->id != $edit) {
                \Html::myalert(l10n::l('not_found', $edit));
            }
        }

        // Defaults
        $defaults = \defaults::getByUsername($_SESSION["user"]);
        if (null === $defaults) {
            $defaults = \defaults::init();
        }
        // Tipos movimento
        $category_id = $edit > 0 ? $edit_entry->category_id : $defaults->category_id;
        $entry_viewer = $viewFactory->entry_category_view(\EntryCategory::getById($category_id));
        $tipo_mov_opt = $entry_viewer->getSelectFromList(\EntryCategory::getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'tipo_id' => ['operator' => '>', 'value' => '0']
        ]));

        // Moedas
        $currency_id = $edit > 0 ? $edit_entry->currency_id : $defaults->currency_id;
        $currency = $objectFactory->currency();
        $currency_viewer = $viewFactory->currency_view($currency);
        $moeda_opt = $currency_viewer->getSelectFromList(\currency::getList(), $currency_id);

        // Contas
        $conta_opt = "";
        $account_id = $edit > 0 ? $edit_entry->account_id : $defaults->account_id;
        $account_viewer = $viewFactory->account_view(\account::getById($account_id));
        $conta_opt = $account_viewer->getSelectFromList(\account::getList(['activa' => ['operator' => '=', 'value' => '1']]), $account_id);
        if (!is_array($filtered_input)) {
            $filtered_input = [];
        }
        $filtered_input2 = [];
        foreach ($filtered_input as $k => $v) {
            if (stristr($k, "filter_")) {
                $filtered_input2[$k] = $v;
            }
        }
        $filtered_input2['lang'] = l10n::$lang;
        $filter_string = http_build_query($filtered_input2);
        ?>
        <div class="header" id="header">
            <form id="datefilter" name="datefilter" action="?lang=<?= l10n::$lang ?>" method="GET">
                <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
                <input type="hidden" name="filter_parent_id"
                    value="<?= !empty($filtered_input["filter_parent_id"]) ? $filtered_input["filter_parent_id"] : "" ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filtered_input["filter_entry_type"]) ? $filtered_input["filter_entry_type"] : "" ?>">
                <table class="filter">
                    <tr>
                        <td><?= l10n::l('start') ?></td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_sdateAA"
                                onchange="update_date('filter_sdate');"><?= \Html::yearOptions(substr($sdate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateMM"
                                onchange="update_date('filter_sdate');"><?= \Html::monthOptions(substr($sdate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateDD"
                                onchange="update_date('filter_sdate');"><?= \Html::dayOptions(substr($sdate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_sdate" name="filter_sdate" required
                                value="<?= (new \DateTime("{$sdate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?= l10n::l('end') ?></td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_edateAA"
                                onchange="update_date('filter_edate');"><?= \Html::yearOptions(substr($edate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateMM"
                                onchange="update_date('filter_edate');"><?= \Html::monthOptions(substr($edate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateDD"
                                onchange="update_date('filter_edate');"><?= \Html::dayOptions(substr($edate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_edate" name="filter_edate" required
                                value="<?= (new \DateTime("{$edate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_account_id"><?= l10n::l('account') ?></label> </td>
                        <td>
                            <select name="filter_account_id" id="filter_account_id"
                                data-placeholder="Seleccione a conta" data-max="2" data-search="false"
                                data-select-all="true" data-list-all="true" data-width="300px" data-height="50px"
                                data-multi-select>
                                <option value><?= l10n::l('no_filter') ?></option>
                                <?= $conta_opt ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_entry_type"><?= l10n::l('category') ?></label></td>
                        <td>
                            <select name="filter_entry_type" id="filter_entry_type">
                                <option value><?= l10n::l('no_filter') ?></option>
                                <?= $tipo_mov_opt ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input class="submit" type="submit" value="<?= l10n::l('filter') ?>">
                            <input class="submit" type="button" value="<?= l10n::l('clear_filter') ?>"
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
            <form name="mov" action="?lang=<?= l10n::$lang ?>" method="POST">
                <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
                <?= CSRF::inputField() ?>
                <input type="hidden" name="filter_account_id"
                    value="<?= !empty($filtered_input["filter_account_id"]) ? $filtered_input["filter_account_id"] : ""; ?>">
                <input type="hidden" name="filter_parent_id"
                    value="<?= !empty($filtered_input["filter_parent_id"]) ? $filtered_input["filter_parent_id"] : ""; ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filtered_input["filter_entry_type"]) ? $filtered_input["filter_entry_type"] : ""; ?>">
                <input type="hidden" name="filter_sdate" value="<?= $sdate; ?>">
                <input type="hidden" name="filter_edate" value="<?= $edate; ?>">
                <div class="table-wrapper">
                    <table class="lista ledger_entry_list">
                        <thead>
                            <tr>
                                <th scope="col"><?= l10n::l('id') ?></th>
                                <th scope="col"><?= l10n::l('date') ?></th>
                                <th scope="col"><?= l10n::l('category') ?></th>
                                <th scope="col"><?= l10n::l('currency') ?></th>
                                <th scope="col"><?= l10n::l('account') ?></th>
                                <th scope="col"><?= l10n::l('dc') ?></th>
                                <th scope="col"><?= l10n::l('amount') ?></th>
                                <th scope="col"><?= l10n::l('remarks') ?></th>
                                <th scope="col"><?= l10n::l('balance') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="balance-label" colspan="8"><?= l10n::l('previous_balance') ?></td>
                                <td data-label="<?= l10n::l('previous_balance') ?>" class="balance">
                                    <?= normalize_number($balance); ?>
                                </td>
                            </tr>
                            <?php

                            foreach ($ledger_entry_cache as $row):
                                print "<tr id='{$row->id}'>";
                                $balance += $row->euro_amount;
                                if ($row->id == $edit) {
                                    ?>
                                    <td data-label=""><input type="hidden" name="id" value="<?= $row->id; ?>">
                                        <input class="submit" type="submit" name="save" value="<?= l10n::l('save') ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('date') ?>" class="id">
                                        <select class="date-fallback" style="display: none" name="data_movAA">
                                            <?= \Html::yearOptions(substr($row->entry_date, 0, 4)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movMM">
                                            <?= \Html::monthOptions(substr($row->entry_date, 5, 2)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movDD">
                                            <?= \Html::dayOptions(substr($row->entry_date, 8, 2)) ?>
                                        </select>
                                        <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                            value="<?= $row->entry_date ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('category') ?>" class="category"><select
                                            name="category_id"><?= $tipo_mov_opt ?></select></td>
                                    <td data-label="<?= l10n::l('currency') ?>" class="currency"><select
                                            name="currency_id"><?= $moeda_opt ?></select>
                                    </td>
                                    <td data-label="<?= l10n::l('account') ?>" class="account"><select
                                            name="account_id"><?= $conta_opt ?></select>
                                    </td>
                                    <td data-label="<?= l10n::l('dc') ?>" class="direction">
                                        <select name="direction">
                                            <option value="1" <?= $row->direction == "1" ? " selected " : "" ?>>
                                                <?= l10n::l('deposit') ?>
                                            </option>
                                            <option value="-1" <?= $row->direction == "-1" ? " selected " : "" ?>>
                                                <?= l10n::l('withdraw') ?>
                                            </option>
                                        </select>
                                    </td>
                                    <td data-label="<?= l10n::l('amount') ?>" class="amount"><input type="number" step="0.01"
                                            name="currency_amount" placeholder="0.00" value="<?= $row->currency_amount ?>"></td>
                                    <td data-label="<?= l10n::l('remarks') ?>" class="remarks"><input type="text"
                                            name="remarks" maxlength="255" value="<?= $row->remarks ?>"></td>
                                    <td data-label="<?= l10n::l('balance') ?>" class="total" style="text-align: right">
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
                                    <td data-label='<?= l10n::l('id') ?>' class='id'><a
                                            title="<?= l10n::l('click_to_edit') ?>&#10;<?= l10n::l('modified_by_at', $row->username, $row->updated_at) ?>"
                                            href="ledger_entries.php?<?= "{$filter_string}&amp;id={$row->id}" ?>"><?= $row->id ?></a>
                                    </td>
                                    <td data-label='<?= l10n::l('date') ?>' class='data'><?= $row->entry_date ?></td>
                                    <td data-label='<?= l10n::l('category') ?>' class='category'><a
                                            title="Filtrar lista para esta categoria"
                                            href="ledger_entries.php?<?= $category_filter ?>"><?= ($row->category->parent_id > 0 ? $row->category->parent_description . "&#8594;" : "") . $row->category->description ?></a>
                                    </td>
                                    <td data-label='<?= l10n::l('currency') ?>' class='currency'>
                                        <?= $row->currency->description ?>
                                    </td>
                                    <td data-label='<?= l10n::l('account') ?>' class='account'><a
                                            title="Filtrar lista para esta conta"
                                            href="ledger_entries.php?<?= $account_filter ?>"><?= $row->account->name ?></a>
                                    </td>
                                    <td data-label='<?= l10n::l('dc') ?>' class='direction'>
                                        <?= $row->direction == "1" ? "Dep" : "Lev" ?>
                                    </td>
                                    <td data-label='<?= l10n::l('amount') ?>' class='amount'>
                                        <?= normalize_number($row->currency_amount) ?>
                                    </td>
                                    <td data-label='<?= l10n::l('remarks') ?>' class='remarks'><?= $row->remarks; ?></td>
                                    <td data-label='<?= l10n::l('balance') ?>' class='total'><?= normalize_number($balance) ?>
                                    </td>
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
                                        <input class="submit" type="submit" name="save" value="<?= l10n::l('save') ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('date') ?>" class="data">
                                        <select class="date-fallback" style="display: none" name="data_movAA">
                                            <?= \Html::yearOptions(substr($defaults->entry_date, 0, 4)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movMM">
                                            <?= \Html::monthOptions(substr($defaults->entry_date, 5, 2)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movDD">
                                            <?= \Html::dayOptions(substr($defaults->entry_date, 8, 2)) ?>
                                        </select>
                                        <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                            value="<?= $defaults->entry_date ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('category') ?>" class="category">
                                        <select name="category_id"> <?= $tipo_mov_opt ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('currency') ?>" class="currency">
                                        <select name="currency_id"> <?= $moeda_opt ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('account') ?>" class="account">
                                        <select name="account_id"> <?= $conta_opt; ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('dc') ?>" class="direction">
                                        <select name="direction">
                                            <option value="1"><?= l10n::l('deposit') ?></option>
                                            <option value="-1" selected><?= l10n::l('withdraw') ?></option>
                                        </select>
                                    </td>
                                    <td data-label="<?= l10n::l('amount') ?>" class="amount">
                                        <input type="number" step="0.01" name="currency_amount" placeholder="0.00"
                                            value="0.00">
                                    </td>
                                    <td data-label="<?= l10n::l('remarks') ?>" class="remarks">
                                        <input type="text" name="remarks" maxlength="255" value="">
                                    </td>
                                    <td data-label="<?= l10n::l('balance') ?>" class="total">
                                        <?= normalize_number($balance) ?>
                                    </td>
                                </tr>
                            </tfoot>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </form>
        </div>
        <div class="main-footer">
            <p><?= l10n::l('transactions_in_period', count($ledger_entry_cache)) ?></p>
        </div>
        <?php
        include_once "footer.php";
        ?>
    </div> <!-- Main grid -->
    <script>
        toggleDateElements("data_mov");
        setTimeout(() => {
            document.getElementById("preloader").style.display = "none"; // Hide
        }, 0);
    </script>
</body>

</html>
