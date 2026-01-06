<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\NumberUtil;

final class LedgerEntriesTableViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <div class="main-container">
            <div class="csv-download">
                <a href="<?= htmlspecialchars($downloadUrl ?? '') ?>"><small><?= $label['download_data'] ?><img src="assets/media/file-csv-solid-full.svg" alt="CSV"></small></a>
            </div>
            <form name="mov" method="POST" lang="<?= $lang ?>">
                <?php foreach ($filters as $k => $v): ?>
                    <?php if ($v !== null): ?>
                        <input type="hidden" name="filter_<?= $k ?>" value="<?= htmlspecialchars(((string)$v) ?? '') ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="table-wrapper">
                    <table class="lista ledger_entry_list">
                        <thead>
                            <tr>
                                <th scope="col"><?= $label['actions'] ?></th>
                                <th scope="col"><?= $label['id'] ?></th>
                                <th scope="col"><?= $label['date'] ?></th>
                                <th scope="col"><?= $label['category'] ?></th>
                                <th scope="col"><?= $label['currency'] ?></th>
                                <th scope="col"><?= $label['account'] ?></th>
                                <th scope="col"><?= $label['dc'] ?></th>
                                <th scope="col"><?= $label['amount'] ?></th>
                                <th scope="col"><?= $label['remarks'] ?></th>
                                <th scope="col"><?= $label['balance'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="balance-label" colspan="9"><?= $label['previous_balance'] ?></td>
                                <td data-label="<?= $label['previous_balance'] ?>" class="balance"><?= NumberUtil::normalize($startBalance); ?></td>
                            </tr>
                            <?php
                            $rowTemplateData = compact('lang', 'label', 'csrf');
                            foreach ($ledgerEntryRows as $row):
                                $rowTemplateData = array_merge(
                                    $rowTemplateData,
                                    [
                                        'text' => $row['text'],
                                        'title' => $row['title'],
                                        'href' => $row['href'],
                                    ]
                                );
                                if ($row['text']['id'] !== $editId) {
                                    $rowTemplateData['row'] = $row;
                                    $rowViewTemplate->render($rowTemplateData);
                                } else {
                                    $rowTemplateData['formData'] = $formData;
                                    $formViewTemplate->render($rowTemplateData);
                                }
                            endforeach;
                            if ((int)($editId ?? 0) === 0) {
                                $rowTemplateData['formData'] = $formData;
                                $rowTemplateData['filters'] = $filters;
                                $formViewTemplate->render($rowTemplateData);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
<?php
    }
}
