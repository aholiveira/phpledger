<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

final class LedgerEntriesMainViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <?php $ui->menu($label, $menu); ?>
        <div class="header main config" id="header">
            <?php $filterViewTemplate->render(compact('lang', 'filterFormData', 'label', 'filters')); ?>
            <script>
                document.getElementById("filter_entryType").value = "<?= !empty($filters["entryType"]) ? $filters["entryType"] : ""; ?>";
                document.getElementById("filter_accountId").value = "<?= !empty($filters["accountId"]) ? $filters["accountId"] : ""; ?>";
            </script>
        </div>
        <div class="main" id="main">
            <?php $tableViewTemplate->render(compact(
                'lang',
                'isEditing',
                'editId',
                'label',
                'formData',
                'startBalance',
                'ledgerEntryRows',
                'filters',
                'csrf',
                'rowViewTemplate',
                'formViewTemplate',
                'downloadUrl'
            )); ?>
        </div>
        <div class="main-footer">
            <p><?= $transactionsInPeriod ?></p>
        </div>
        <?php $ui->footer($label, $footer); ?>
        </div> <!-- Main grid -->
        <script>
            setTimeout(() => {
                document.getElementById("preloader").style.display = "none"; // Hide
            }, 0);
            focusTarget("data_mov");
        </script>
        </body>

        </html>
<?php
    }
}
