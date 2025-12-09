<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Domain\LedgerEntry;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\NumberUtil;
use PHPLedger\Views\Templates\LedgerEntryViewTemplate;

final class LedgerEntriesView
{
    private ApplicationObjectInterface $app;
    private array $data;
    public function render(ApplicationObjectInterface $app, array $data): void
    {
        $this->app = $app;
        $this->data = $data;
        $action = $data['action'];
        $filteredInput = $data['filteredInput'];
        $savedEntryId = $data['savedEntryId'] ?? 0;
        $success = $data['success'];
        $errorMessage = $data['errorMessage'];
        $ledgerEntryCache = $data['ledgerEntryList'];
        $pagetitle = $this->app->l10n()->l("ledger_entries");
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
            <script src="assets/ledger_entries.js"> </script>
        </head>

        <body>
            <?php if ($savedEntryId > 0): ?>
                <div id="notification" class="notification <?= $success ? "success" : "fail" ?>">
                    <?= $success ? $this->app->l10n()->l("save_success", $savedEntryId) : $errorMessage ?>
                </div>
                <script>
                    const el = document.getElementById('notification');
                    setTimeout(() => {
                        el.classList.add('hide');
                        el.addEventListener('transitionend', () => el.remove(), {
                            once: true
                        });
                    }, 2500);
                </script>
            <?php endif ?>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div class="header main config" id="header">
                    <?php $this->printFilter(); ?>
                    <script>
                        document.getElementById("filter_entryType").value = "<?= !empty($filteredInput["filter_entryType"]) ? $filteredInput["filter_entryType"] : ""; ?>";
                        document.getElementById("filter_accountId").value = "<?= !empty($filteredInput["filter_accountId"]) ? $filteredInput["filter_accountId"] : ""; ?>";
                    </script>
                </div>
                <div class="main" id="main">
                    <?php $this->printBody(); ?>
                </div>
                <div class="main-footer">
                    <p><?= $this->app->l10n()->l('transactions_in_period', count($ledgerEntryCache)) ?></p>
                </div>
                <?php Html::footer($this->app, $action); ?>
            </div> <!-- Main grid -->
            <script>
                toggleDateElements("data_mov");
                setTimeout(() => {
                    document.getElementById("preloader").style.display = "none"; // Hide
                }, 0);
            </script>
        </body>

        </html>
    <?php
    }
    private function printFilter()
    {
        $filters = $this->data['filters'] ?? [];
        $startDate = $filters['startDate'] ?? date("Y-m-01");
        $endDate = $filters['endDate'] ?? date("Y-m-d");
        $accountSelectOptions = $this->data['accountSelectOptions'] ?? [];
        $entryTypesSelectOptions = $this->data['entryTypesSelectOptions'] ?? [];
    ?>
        <form id="datefilter" name="datefilter" action="?lang=<?= $this->app->l10n()->lang() ?>" method="GET">
            <input name="action" value="ledger_entries" type="hidden">
            <input name="lang" value="<?= $this->app->l10n()->lang() ?>" type="hidden">
            <input type="hidden" name="filter_parentId" value="<?= !empty($filters["parentId"]) ? $filters["parentId"] : "" ?>">
            <input type="hidden" name="filter_entryType" value="<?= !empty($filters["entryType"]) ? $filters["entryType"] : "" ?>">
            <p>
                <label for="filter_startDate"><?= $this->app->l10n()->l('start') ?></label>
                <span id="filter_startDate">
                    <select class="date-fallback" style="display: none" name="filter_startDateAA"
                        onchange="update_date('filter_startDate');"><?= Html::yearOptions(substr($startDate, 0, 4)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_startDateMM"
                        onchange="update_date('filter_startDate');"><?= Html::monthOptions(substr($startDate, 5, 2)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_startDateDD"
                        onchange="update_date('filter_startDate');"><?= Html::dayOptions(substr($startDate, 8, 2)) ?></select>
                    <input class="date-fallback" type="date" name="filter_startDate" required
                        value="<?= $startDate ?>">
                </span>
            </p>
            <p>
                <label for="filter_endDate"><?= $this->app->l10n()->l('end') ?></label>
                <span id="filter_endDate">
                    <select class="date-fallback" style="display: none" name="filter_endDateAA"
                        onchange="update_date('filter_endDate');"><?= Html::yearOptions(substr($endDate, 0, 4)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_endDateMM"
                        onchange="update_date('filter_endDate');"><?= Html::monthOptions(substr($endDate, 5, 2)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_endDateDD"
                        onchange="update_date('filter_endDate');"><?= Html::dayOptions(substr($endDate, 8, 2)) ?></select>
                    <input class="date-fallback" type="date" name="filter_endDate" required
                        value="<?= $endDate ?>">
                </span>
            </p>
            <p>
                <label for="filter_accountId"><?= $this->app->l10n()->l('account') ?></label>
                <select name="filter_accountId" id="filter_accountId" data-placeholder="Seleccione a conta"
                    data-max="2" data-search="false" data-select-all="true" data-list-all="true"
                    data-width="300px" data-height="50px" data-multi-select>
                    <option value><?= $this->app->l10n()->l('no_filter') ?></option>
                    <?= $accountSelectOptions ?>
                </select>
            </p>
            <p>
                <label for="filter_entryType"><?= $this->app->l10n()->l('category') ?></label>
                <select name="filter_entryType" id="filter_entryType">
                    <option value><?= $this->app->l10n()->l('no_filter') ?></option>
                    <?= $entryTypesSelectOptions ?>
                </select>
            </p>
            <p>
                <span style="grid-column: 2 / 2;">
                    <input class="submit" type="submit" value="<?= $this->app->l10n()->l('filter') ?>">
                    <input class="submit" type="button" value="<?= $this->app->l10n()->l('clear_filter') ?>"
                        onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();">
                </span>
            </p>
        </form>
    <?php
    }
    private function printBody()
    {
        $filters = $this->data['filters'];
        $startDate = $filters['startDate'] ?? date("Y-m-01");
        $endDate = $filters['endDate'] ?? date("Y-m-d");
        $balance = (float)($this->data['balance'] ?? 0);
        $ledgerEntryCache = $this->data['ledgerEntryList'];
        $lang = $this->app->l10n()->lang();
        $editId = is_numeric($filters['id'] ?? '') ? $filters['id'] : 0;
    ?>
        <form name="mov" method="POST" lang="<?= $lang ?>">
            <?= CSRF::inputField() ?>
            <input name="lang" value="<?= $this->app->l10n()->lang() ?>" type="hidden" />
            <input name="action" type="hidden" value="ledger_entries" />
            <input type="hidden" name="filter_accountId" value="<?= !empty($filters["accountId"]) ? $filters["accountId"] : ""; ?>">
            <input type="hidden" name="filter_parentId" value="<?= !empty($filters["parentId"]) ? $filters["parentId"] : ""; ?>">
            <input type="hidden" name="filter_entryType" value="<?= !empty($filters["entryType"]) ? $filters["entryType"] : ""; ?>">
            <input type="hidden" name="filter_startDate" value="<?= $startDate; ?>">
            <input type="hidden" name="filter_endDate" value="<?= $endDate; ?>">
            <div class="table-wrapper">
                <table class="lista ledger_entry_list">
                    <thead>
                        <tr>
                            <th scope="col"><?= $this->app->l10n()->l('id') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('date') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('category') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('currency') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('account') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('dc') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('amount') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('remarks') ?></th>
                            <th scope="col"><?= $this->app->l10n()->l('balance') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="balance-label" colspan="8"><?= $this->app->l10n()->l('previous_balance') ?></td>
                            <td data-label="<?= $this->app->l10n()->l('previous_balance') ?>" class="balance">
                                <?= NumberUtil::normalize($balance); ?>
                            </td>
                        </tr>
                        <?php
                        foreach ($ledgerEntryCache as $row):
                            $balance += $row->euroAmount;
                            if ($row->id !== $editId) {
                                $this->printRow($row, $filters, $lang, $balance);
                            } else {
                                $this->printEditor($row, $balance);
                            }
                        endforeach;
                        if ($editId === 0) {
                            $this->printEditor(null, $balance);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php
    }
    private function printRow(LedgerEntry $row, array $filters, string $lang, float $balance)
    {
        $baseLink = "index.php?action=ledger_entries&";
        $filtersArray = array_combine(
            array_map(fn($k) => "filter_$k", array_keys($filters)),
            array_values($filters)
        );
        $idQuery = http_build_query(array_merge($filtersArray, ['id' => $row->id]));
        $categoryQuery = http_build_query(array_merge($filtersArray, ['filter_entryType' => $row->categoryId]));
        $accountQuery = http_build_query(array_merge($filtersArray, ['filter_accountId' => $row->accountId]));
        $templateData['lang'] = $lang;
        $templateData['rowId'] = $row->id;
        $templateData['label'] = [
            'id' => $this->app->l10n()->l('id'),
            'date' => $this->app->l10n()->l('date'),
            'category' => $this->app->l10n()->l('category'),
            'currency' => $this->app->l10n()->l('currency'),
            'account' => $this->app->l10n()->l('account'),
            'dc' => $this->app->l10n()->l('dc'),
            'amount' => $this->app->l10n()->l('amount'),
            'remarks' => $this->app->l10n()->l('remarks'),
            'balance' => $this->app->l10n()->l('balance'),
        ];
        $templateData['text'] = [
            'id' => $row->id,
            'date' => $row->entryDate,
            'category' => ($row->category->parentId > 0 ? $row->category->parentDescription . "&#8594;" : "") . $row->category->description,
            'currency' => $row->currency->description,
            'account' => $row->account->name,
            'dc' => $row->direction == "1" ? "Dep" : "Lev",
            'amount' => NumberUtil::normalize($row->currencyAmount),
            'remarks' => $row->remarks,
            'balance' => NumberUtil::normalize($balance)
        ];
        $templateData['href'] = [
            'id' => "{$baseLink}{$idQuery}",
            'category' => "{$baseLink}{$categoryQuery}",
            'account' => "{$baseLink}{$accountQuery}"
        ];
        $templateData['title'] = [
            'id' => "{$this->app->l10n()->l('click_to_edit')}&#10;{$this->app->l10n()->l('modified_by_at',$row->username,$row->updatedAt)}",
            'category' => "Filtrar lista para esta categoria",
            'account' => "Filtrar lista para esta conta",
        ];
        $rowTemplate = new LedgerEntryViewTemplate();
        $rowTemplate->render($templateData);
    }
    private function printEditor(?LedgerEntry $row, float $balance)
    {
        $defaults = $this->data['defaults'];
        $accountSelectOptions = $this->data['accountSelectOptions'];
        $currencySelectOptions = $this->data['currencySelectOptions'];
        $entryTypesSelectOptions = $this->data['entryTypesSelectOptions'];
        $editRow['id'] = $row !== null ? $row->id : "NULL";
        $editRow['date'] = $row !== null ? $row->entryDate : $defaults->entryDate;
        $editRow['amount'] = $row !== null ? $row->currencyAmount : 0.00;
        $editRow['direction'] = $row !== null ? $row->direction : 0.00;
        $editRow['remarks'] = $row !== null ? $row->remarks : "";
        if ($row === null) {
        ?>
            <tfoot>
            <?php
        }
            ?>
            <tr>
                <td data-label=""><input type="hidden" name="id" value="<?= $editRow['id'] ?>">
                    <input class="submit" type="submit" name="save" value="<?= $this->app->l10n()->l('save') ?>">
                </td>
                <td data-label="<?= $this->app->l10n()->l('date') ?>" class="id">
                    <select class="date-fallback" style="display: none" name="data_movAA">
                        <?= Html::yearOptions(substr($editRow['date'], 0, 4)) ?>
                    </select>
                    <select class="date-fallback" style="display: none" name="data_movMM">
                        <?= Html::monthOptions(substr($editRow['date'], 5, 2)) ?>
                    </select>
                    <select class="date-fallback" style="display: none" name="data_movDD">
                        <?= Html::dayOptions(substr($editRow['date'], 8, 2)) ?>
                    </select>
                    <input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?= $editRow['date'] ?>">
                </td>
                <td data-label="<?= $this->app->l10n()->l('category') ?>" class="category"><select
                        name="categoryId"><?= $entryTypesSelectOptions ?></select></td>
                <td data-label="<?= $this->app->l10n()->l('currency') ?>" class="currency"><select
                        name="currencyId"><?= $currencySelectOptions ?></select>
                </td>
                <td data-label="<?= $this->app->l10n()->l('account') ?>" class="account"><select
                        name="accountId"><?= $accountSelectOptions ?></select>
                </td>
                <td data-label="<?= $this->app->l10n()->l('dc') ?>" class="direction">
                    <select name="direction">
                        <option value="1" <?= (int)$editRow['direction'] === 1 ? " selected " : "" ?>>
                            <?= $this->app->l10n()->l('deposit') ?>
                        </option>
                        <option value="-1" <?= (int)$editRow['direction'] === -1 ? " selected " : "" ?>>
                            <?= $this->app->l10n()->l('withdraw') ?>
                        </option>
                    </select>
                </td>
                <td data-label="<?= $this->app->l10n()->l('amount') ?>" class="amount"><input type="number" step="0.01"
                        name="currencyAmount" placeholder="0.00" value="<?= $editRow['amount'] ?>"></td>
                <td data-label="<?= $this->app->l10n()->l('remarks') ?>" class="remarks"><input type="text" name="remarks"
                        maxlength="255" value="<?= $editRow['remarks'] ?>"></td>
                <td data-label="<?= $this->app->l10n()->l('balance') ?>" class="total" style="text-align: right">
                    <?= NumberUtil::normalize($balance) ?>
                </td>
            </tr>
            <?php
            if ($row === null) {
            ?>
            </tfoot>
        <?php
            }
        ?>
<?php
    }
}
