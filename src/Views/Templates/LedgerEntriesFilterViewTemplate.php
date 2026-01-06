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
        <form id="datefilter" name="datefilter" action="?lang=<?= $lang ?>" method="GET">
            <input name="action" value="ledger_entries" type="hidden">
            <input name="lang" value="<?= $lang ?>" type="hidden">
            <p>
                <label for="filter_startDateSpan"><?= $label['start'] ?></label>
                <input type="date" id="filter_startDate" name="filter_startDate" required="" value="<?= $filterFormData['startDate'] ?>">
            </p>
            <p>
                <label for="filter_endDateSpan"><?= $label['end'] ?></label>
                <input type="date" id="filter_endDate" name="filter_endDate" required="" value="<?= $filterFormData['endDate'] ?>">
            </p>
            <p>
                <label for="filter_accountId"><?= $label['account'] ?></label>
                <select name="filter_accountId" id="filter_accountId" data-placeholder="Seleccione a conta"
                    data-max="2" data-search="false" data-select-all="true" data-list-all="true"
                    data-width="300px" data-height="50px" data-multi-select>
                    <option value><?= $label['no_filter'] ?></option>
                    <?php $this->renderSelectOptions($filterFormData['accounts']); ?>
                </select>
            </p>
            <p>
                <label for="filter_entryType"><?= $label['category'] ?></label>
                <select name="filter_entryType" id="filter_entryType">
                    <option value><?= $label['no_filter'] ?></option>
                    <?php $this->renderSelectOptions($filterFormData['entryCategory']); ?>
                </select>
            </p>
            <p>
                <span style="grid-column: 2 / 2;">
                    <input class="submit" type="submit" value="<?= $label['filter'] ?>">
                    <input class="submit" type="button" value="<?= $label['clear_filter'] ?>"
                        onclick="clear_filter(); document.getElementById('datefilter').requestSubmit();">
                </span>
            </p>
        </form>
<?php
    }
}
