<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Controllers\LedgerEntryController;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\ViewFactory;

if (!defined("ROOT_DIR")) {
    require_once __DIR__ . "/prepend.php";
}

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
    'accountId' => FILTER_SANITIZE_NUMBER_INT,
    'categoryId' => FILTER_SANITIZE_NUMBER_INT,
    'currencyAmount' => [
        'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
    ],
    'currencyId' => FILTER_SANITIZE_ENCODED,
    'direction' => FILTER_SANITIZE_NUMBER_INT,
    'remarks' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    'filter_entry_type' => FILTER_SANITIZE_NUMBER_INT,
    'filter_accountId' => FILTER_SANITIZE_NUMBER_INT,
    'filter_parentId' => FILTER_SANITIZE_NUMBER_INT,
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
$filteredInput = [];
$savedEntryId = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('ledger_entries.php');
    }
    $filteredInput = filter_input_array(INPUT_POST, $input_variables_filter, true);
    try {
        $savedEntryId = (new LedgerEntryController())->handleSave($filteredInput);
        $success = true;
    } catch (\Exception $e) {
        $error_essage = $e->getMessage();
        $success = false;
    }
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $filteredInput = filter_input_array(INPUT_GET, $input_variables_filter, true);
}

?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header($pagetitle); ?>
    <script src="ledger_entries.js"> </script>
</head>

<body>
    <?php if (!empty($savedEntryId)): ?>
        <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
            <?= $success ? l10n::l("save_success", $savedEntryId) : $error_essage ?>
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
        <?php Html::menu();
        if (!empty($filteredInput["filter_sdate"])) {
            $sdate = strlen($filteredInput["filter_sdate"]) ? $filteredInput["filter_sdate"] : date("Y-m-01");
        } else {
            if (!empty($filteredInput["filter_sdateAA"])) {
                $sdate = sprintf("%04d-%02d-%02d", $filteredInput["filter_sdateAA"], $filteredInput["filter_sdateMM"], $filteredInput["filter_sdateDD"]);
            } else {
                $sdate = date("Y-m-01");
            }
        }
        if (!empty($filteredInput["filter_edate"])) {
            $edate = strlen($filteredInput["filter_edate"]) ? str_replace("-", "", $filteredInput["filter_edate"]) : date("Ymd");
        } else {
            if (is_array($filteredInput) && !empty($filteredInput["filter_edateAA"])) {
                $edate = sprintf("%04d-%02d-%02d", $filteredInput["filter_edateAA"], $filteredInput["filter_edateMM"], $filteredInput["filter_edateDD"]);
            } else {
                $edate = date("Y-m-d");
            }
        }
        $ledgerFilter[] = ["entryDate" => ["operator" => '>=', "value" => $sdate]];
        $ledgerFilter[] = ["entryDate" => ["operator" => '<=', "value" => $edate]];
        if (!empty($filteredInput["filter_accountId"])) {
            $ledgerFilter[] = ['accountId' => ["operator" => '=', "value" => $filteredInput["filter_accountId"]]];
        }
        if (!empty($filteredInput["filter_entry_type"])) {
            $ledgerFilter[] = ['categoryId' => ["operator" => '=', "value" => $filteredInput["filter_entry_type"]]];
        }
        $filter = "movimentos.entryDate>='{$sdate}' AND movimentos.entryDate<='{$edate}'";
        $parentFilter = "";
        if (!empty($filteredInput["filter_parentId"])) {
            $parentFilter = "tipo_mov.parentId={$filteredInput['filter_parentId']} ";
            //$ledgerFilter[] = ["parentId" => ["operator" => "IN", "value" => "({$filteredInput['filter_parentId']})"]];
        }
        $edit = 0;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && is_array($filteredInput) && !empty($filteredInput["id"])) {
            $edit = $filteredInput["id"];
        }

        // Saldo anterior
        $ledgerEntry = ObjectFactory::ledgerentry();
        $balance = $ledgerEntry->getBalanceBeforeDate($sdate, is_array($filteredInput) && $filteredInput["filter_accountId"] > 0 ? $filteredInput["filter_accountId"] : null);
        $ledgerEntryCache = ObjectFactory::ledgerEntry()::getList($ledgerFilter);
        $entry_filter_array = [];
        if ($edit > 0) {
            $editEntry = ObjectFactory::ledgerEntry()::getById($edit);
            if ($editEntry->id != $edit) {
                die(l10n::l('not_found', $edit));
            }
            $ledgerEntry = ObjectFactory::ledgerEntry()::getById($edit);
            if ($ledgerEntry->id != $edit) {
                Html::myalert(l10n::l('not_found', $edit));
            }
        }

        // Defaults
        $defaults = ObjectFactory::defaults()::getByUsername($_SESSION["user"]);
        if (null === $defaults) {
            $defaults = ObjectFactory::defaults()::init();
        }
        // Tipos movimento
        $categoryId = $edit > 0 ? $editEntry->categoryId : $defaults->categoryId;
        $entry_viewer = ViewFactory::instance()->entryCategoryView(ObjectFactory::entryCategory()::getById($categoryId));
        $tipo_mov_opt = $entry_viewer->getSelectFromList(ObjectFactory::entryCategory()::getList([
            'active' => ['operator' => '=', 'value' => '1'],
            'id' => ['operator' => '>', 'value' => '0']
        ]));

        // Moedas
        $currencyId = $edit > 0 ? $editEntry->currencyId : $defaults->currencyId;
        $currency = ObjectFactory::currency();
        $currencyViewer = ViewFactory::instance()->currencyView($currency);
        $moeda_opt = $currencyViewer->getSelectFromList(ObjectFactory::currency()::getList(), $currencyId);

        // Contas
        $conta_opt = "";
        $accountId = $edit > 0 ? $editEntry->accountId : $defaults->accountId;
        $accountViewer = ViewFactory::instance()->accountView(ObjectFactory::account()::getById($accountId));
        $conta_opt = $accountViewer->getSelectFromList(ObjectFactory::account()::getList(['activa' => ['operator' => '=', 'value' => '1']]), $accountId);
        if (!is_array($filteredInput)) {
            $filteredInput = [];
        }
        $filteredInput2 = [];
        foreach ($filteredInput as $k => $v) {
            if (stristr($k, "filter_")) {
                $filteredInput2[$k] = $v;
            }
        }
        $filteredInput2['lang'] = l10n::$lang;
        $filter_string = http_build_query($filteredInput2);
        ?>
        <div class="header" id="header">
            <form id="datefilter" name="datefilter" action="?lang=<?= l10n::$lang ?>" method="GET">
                <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
                <input type="hidden" name="filter_parentId"
                    value="<?= !empty($filteredInput["filter_parentId"]) ? $filteredInput["filter_parentId"] : "" ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filteredInput["filter_entry_type"]) ? $filteredInput["filter_entry_type"] : "" ?>">
                <table class="filter">
                    <tr>
                        <td><?= l10n::l('start') ?></td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_sdateAA"
                                onchange="update_date('filter_sdate');"><?= Html::yearOptions(substr($sdate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateMM"
                                onchange="update_date('filter_sdate');"><?= Html::monthOptions(substr($sdate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_sdateDD"
                                onchange="update_date('filter_sdate');"><?= Html::dayOptions(substr($sdate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_sdate" name="filter_sdate" required
                                value="<?= (new \DateTime("{$sdate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?= l10n::l('end') ?></td>
                        <td>
                            <select class="date-fallback" style="display: none" name="filter_edateAA"
                                onchange="update_date('filter_edate');"><?= Html::yearOptions(substr($edate, 0, 4)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateMM"
                                onchange="update_date('filter_edate');"><?= Html::monthOptions(substr($edate, 5, 2)) ?></select>
                            <select class="date-fallback" style="display: none" name="filter_edateDD"
                                onchange="update_date('filter_edate');"><?= Html::dayOptions(substr($edate, 8, 2)) ?></select>
                            <input class="date-fallback" type="date" id="filter_edate" name="filter_edate" required
                                value="<?= (new \DateTime("{$edate}"))->format("Y-m-d") ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="filter_accountId"><?= l10n::l('account') ?></label> </td>
                        <td>
                            <select name="filter_accountId" id="filter_accountId" data-placeholder="Seleccione a conta"
                                data-max="2" data-search="false" data-select-all="true" data-list-all="true"
                                data-width="300px" data-height="50px" data-multi-select>
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
                document.getElementById("filter_entry_type").value = "<?= !empty($filteredInput["filter_entry_type"]) ? $filteredInput["filter_entry_type"] : ""; ?>";
                document.getElementById("filter_accountId").value = "<?= !empty($filteredInput["filter_accountId"]) ? $filteredInput["filter_accountId"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <form name="mov" action="?lang=<?= l10n::$lang ?>" method="POST">
                <input name="lang" value="<?= l10n::$lang ?>" type="hidden" />
                <?= CSRF::inputField() ?>
                <input type="hidden" name="filter_accountId"
                    value="<?= !empty($filteredInput["filter_accountId"]) ? $filteredInput["filter_accountId"] : ""; ?>">
                <input type="hidden" name="filter_parentId"
                    value="<?= !empty($filteredInput["filter_parentId"]) ? $filteredInput["filter_parentId"] : ""; ?>">
                <input type="hidden" name="filter_entry_type"
                    value="<?= !empty($filteredInput["filter_entry_type"]) ? $filteredInput["filter_entry_type"] : ""; ?>">
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
                                    <?= normalizeNumber($balance); ?>
                                </td>
                            </tr>
                            <?php

                            foreach ($ledgerEntryCache as $row):
                                print "<tr id='{$row->id}'>";
                                $balance += $row->euroAmount;
                                if ($row->id == $edit) {
                                    ?>
                                    <td data-label=""><input type="hidden" name="id" value="<?= $row->id; ?>">
                                        <input class="submit" type="submit" name="save" value="<?= l10n::l('save') ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('date') ?>" class="id">
                                        <select class="date-fallback" style="display: none" name="data_movAA">
                                            <?= Html::yearOptions(substr($row->entryDate, 0, 4)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movMM">
                                            <?= Html::monthOptions(substr($row->entryDate, 5, 2)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movDD">
                                            <?= Html::dayOptions(substr($row->entryDate, 8, 2)) ?>
                                        </select>
                                        <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                            value="<?= $row->entryDate ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('category') ?>" class="category"><select
                                            name="categoryId"><?= $tipo_mov_opt ?></select></td>
                                    <td data-label="<?= l10n::l('currency') ?>" class="currency"><select
                                            name="currencyId"><?= $moeda_opt ?></select>
                                    </td>
                                    <td data-label="<?= l10n::l('account') ?>" class="account"><select
                                            name="accountId"><?= $conta_opt ?></select>
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
                                            name="currencyAmount" placeholder="0.00" value="<?= $row->currencyAmount ?>"></td>
                                    <td data-label="<?= l10n::l('remarks') ?>" class="remarks"><input type="text" name="remarks"
                                            maxlength="255" value="<?= $row->remarks ?>"></td>
                                    <td data-label="<?= l10n::l('balance') ?>" class="total" style="text-align: right">
                                        <?= normalizeNumber($balance) ?>
                                    </td>
                                    <?php
                                }
                                if (empty($edit) || $row->id != $edit) {
                                    $filteredInput3 = $filteredInput2;
                                    $filteredInput3["filter_entry_type"] = $row->categoryId;
                                    $category_filter = http_build_query($filteredInput3);
                                    $filteredInput3 = $filteredInput2;
                                    $filteredInput3["filter_accountId"] = $row->accountId;
                                    $account_filter = http_build_query($filteredInput3);
                                    ?>
                                    <td data-label='<?= l10n::l('id') ?>' class='id'><a
                                            title="<?= l10n::l('click_to_edit') ?>&#10;<?= l10n::l('modified_by_at', $row->username, $row->updatedAt) ?>"
                                            href="ledger_entries.php?<?= "{$filter_string}&amp;id={$row->id}" ?>"><?= $row->id ?></a>
                                    </td>
                                    <td data-label='<?= l10n::l('date') ?>' class='data'><?= $row->entryDate ?></td>
                                    <td data-label='<?= l10n::l('category') ?>' class='category'><a
                                            title="Filtrar lista para esta categoria"
                                            href="ledger_entries.php?<?= $category_filter ?>"><?= ($row->category->parentId > 0 ? $row->category->parentDescription . "&#8594;" : "") . $row->category->description ?></a>
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
                                        <?= normalizeNumber($row->currencyAmount) ?>
                                    </td>
                                    <td data-label='<?= l10n::l('remarks') ?>' class='remarks'><?= $row->remarks; ?></td>
                                    <td data-label='<?= l10n::l('balance') ?>' class='total'><?= normalizeNumber($balance) ?>
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
                                            <?= Html::yearOptions(substr($defaults->entryDate, 0, 4)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movMM">
                                            <?= Html::monthOptions(substr($defaults->entryDate, 5, 2)) ?>
                                        </select>
                                        <select class="date-fallback" style="display: none" name="data_movDD">
                                            <?= Html::dayOptions(substr($defaults->entryDate, 8, 2)) ?>
                                        </select>
                                        <input class="date-fallback" type="date" id="data_mov" name="data_mov" required
                                            value="<?= $defaults->entryDate ?>">
                                    </td>
                                    <td data-label="<?= l10n::l('category') ?>" class="category">
                                        <select name="categoryId"> <?= $tipo_mov_opt ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('currency') ?>" class="currency">
                                        <select name="currencyId"> <?= $moeda_opt ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('account') ?>" class="account">
                                        <select name="accountId"> <?= $conta_opt; ?> </select>
                                    </td>
                                    <td data-label="<?= l10n::l('dc') ?>" class="direction">
                                        <select name="direction">
                                            <option value="1"><?= l10n::l('deposit') ?></option>
                                            <option value="-1" selected><?= l10n::l('withdraw') ?></option>
                                        </select>
                                    </td>
                                    <td data-label="<?= l10n::l('amount') ?>" class="amount">
                                        <input type="number" step="0.01" name="currencyAmount" placeholder="0.00"
                                            value="0.00">
                                    </td>
                                    <td data-label="<?= l10n::l('remarks') ?>" class="remarks">
                                        <input type="text" name="remarks" maxlength="255" value="">
                                    </td>
                                    <td data-label="<?= l10n::l('balance') ?>" class="total">
                                        <?= normalizeNumber($balance) ?>
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
            <p><?= l10n::l('transactions_in_period', count($ledgerEntryCache)) ?></p>
        </div>
        <?php Html::footer(); ?>
    </div> <!-- Main grid -->
    <script>
        toggleDateElements("data_mov");
        setTimeout(() => {
            document.getElementById("preloader").style.display = "none"; // Hide
        }, 0);
    </script>
</body>

</html>
