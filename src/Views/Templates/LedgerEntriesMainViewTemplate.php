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

final class LedgerEntriesMainViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <?php Html::menu($l10n, $isAdmin); ?>
        <div class="header main config" id="header">
            <?php (new LedgerEntriesFilterViewTemplate)->render(compact('l10n', 'lang', 'filterFormData', 'labels', 'filters')); ?>
            <script>
                document.getElementById("filter_entryType").value = "<?= !empty($filters["entryType"]) ? $filters["entryType"] : ""; ?>";
                document.getElementById("filter_accountId").value = "<?= !empty($filters["accountId"]) ? $filters["accountId"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <?php (new LedgerEntriesTableViewTemplate)->render([
                'lang' => $lang,
                'isEditing' => $isEditing,
                'editId' => $editId,
                'labels' => $labels,
                'formData' => $formData,
                'startBalance' => $startBalance,
                'ledgerEntryRows' => $ledgerEntryRows,
                'filters' => $filters
            ]); ?>
        </div>
        <div class="main-footer">
            <p><?= $transactionsInPeriod ?></p>
        </div>
        <?php Html::footer($app, $action); ?>
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
}
