<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class LedgerEntriesFilterViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <form id="datefilter" name="datefilter" action="?lang=<?= $l10n->lang() ?>" method="GET">
            <input name="action" value="ledger_entries" type="hidden">
            <input name="lang" value="<?= $lang ?>" type="hidden">
            <p>
                <label for="filter_startDateSpan"><?= $labels['start'] ?></label>
                <span id="filter_startDateSpan">
                    <select class="date-fallback" style="display: none" name="filter_startDateAA"
                        onchange="update_date('filter_startDate');"><?= Html::yearOptions(substr($filterFormData['startDate'], 0, 4)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_startDateMM"
                        onchange="update_date('filter_startDate');"><?= Html::monthOptions(substr($filterFormData['startDate'], 5, 2)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_startDateDD"
                        onchange="update_date('filter_startDate');"><?= Html::dayOptions(substr($filterFormData['startDate'], 8, 2)) ?></select>
                    <input class="date-fallback" type="date" id="filter_startDate" name="filter_startDate" required="" value="<?= $filterFormData['startDate'] ?>">
                </span>
            </p>
            <p>
                <label for="filter_endDateSpan"><?= $labels['end'] ?></label>
                <span id="filter_endDateSpan">
                    <select class="date-fallback" style="display: none" name="filter_endDateAA"
                        onchange="update_date('filter_endDate');"><?= Html::yearOptions(substr($filterFormData['endDate'], 0, 4)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_endDateMM"
                        onchange="update_date('filter_endDate');"><?= Html::monthOptions(substr($filterFormData['endDate'], 5, 2)) ?></select>
                    <select class="date-fallback" style="display: none" name="filter_endDateDD"
                        onchange="update_date('filter_endDate');"><?= Html::dayOptions(substr($filterFormData['endDate'], 8, 2)) ?></select>
                    <input class="date-fallback" type="date" id="filter_endDate" name="filter_endDate" required="" value="<?= $filterFormData['endDate'] ?>">
                </span>
            </p>
            <p>
                <label for="filter_accountId"><?= $labels['account'] ?></label>
                <select name="filter_accountId" id="filter_accountId" data-placeholder="Seleccione a conta"
                    data-max="2" data-search="false" data-select-all="true" data-list-all="true"
                    data-width="300px" data-height="50px" data-multi-select>
                    <option value><?= $labels['no_filter'] ?></option>
                    <?php $this->renderSelectOptions($filterFormData['accounts']); ?>
                </select>
            </p>
            <p>
                <label for="filter_entryType"><?= $labels['category'] ?></label>
                <select name="filter_entryType" id="filter_entryType">
                    <option value><?= $labels['no_filter'] ?></option>
                    <?php $this->renderSelectOptions($filterFormData['entryCategory']); ?>
                </select>
            </p>
            <p>
                <span style="grid-column: 2 / 2;">
                    <input class="submit" type="submit" value="<?= $labels['filter'] ?>">
                    <input class="submit" type="button" value="<?= $labels['clear_filter'] ?>"
                        onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();">
                </span>
            </p>
        </form>
<?php
    }
}
