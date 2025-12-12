<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\NumberUtil;

final class LedgerEntriesTableViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <div class="table-wrapper">
            <form name="mov" method="POST" lang="<?= $lang ?>">
                <table class="lista ledger_entry_list">
                    <thead>
                        <tr>
                            <th scope="col"><?= $labels['action'] ?></th>
                            <th scope="col"><?= $labels['id'] ?></th>
                            <th scope="col"><?= $labels['date'] ?></th>
                            <th scope="col"><?= $labels['category'] ?></th>
                            <th scope="col"><?= $labels['currency'] ?></th>
                            <th scope="col"><?= $labels['account'] ?></th>
                            <th scope="col"><?= $labels['dc'] ?></th>
                            <th scope="col"><?= $labels['amount'] ?></th>
                            <th scope="col"><?= $labels['remarks'] ?></th>
                            <th scope="col"><?= $labels['balance'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="balance-label" colspan="9"><?= $labels['previous_balance'] ?></td>
                            <td data-label="<?= $labels['previous_balance'] ?>" class="balance">
                                <?= NumberUtil::normalize($startBalance); ?>
                            </td>
                        </tr>
                        <?php
                        $rowTemplate = new LedgerEntriesRowViewTemplate();
                        $formTemplate = new LedgerEntriesFormViewTemplate();
                        $rowTemplateData = [
                            'lang' => $lang,
                            'labels' => $labels
                        ];
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
                                $rowTemplate->render($rowTemplateData);
                            } else {
                                $rowTemplateData['formData'] = $formData;
                                $formTemplate->render($rowTemplateData);
                            }
                        endforeach;
                        if (!($editId > 0)) {
                            $rowTemplateData['formData'] = $formData;
                            $rowTemplateData['filters'] = $filters;
                            $formTemplate->render($rowTemplateData);
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
<?php
    }
}
