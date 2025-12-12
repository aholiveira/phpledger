<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views\Templates;

final class LedgerEntriesRowViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <tr id="<?= $text['id'] ?>">
            <td lang="<?= $lang ?>" data-label="<?= $labels['editlink'] ?>" class="id"><a title="<?= $title['editlink'] ?>" href="<?= $href['editlink'] ?>"><?= $labels['editlink'] ?></a></td>
            <td lang="<?= $lang ?>" data-label="<?= $labels['id'] ?>" class="id"><?= $text['id'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $labels['date'] ?>" class="data"><?= $text['date'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $labels['category'] ?>" class="category">
                <a title="<?= $title['category'] ?>" href="<?= $href['category'] ?>"><?= $text['category'] ?></a>
            </td>
            <td lang="<?= $lang ?>" data-label="<?= $labels['currency'] ?>" class="currency"><?= $text['currency'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $labels['account'] ?>" class="account">
                <a title="<?= $title['account'] ?>" href="<?= $href['account'] ?>"><?= $text['account'] ?></a>
            </td>
            <td data-label="<?= $labels['dc'] ?>" class="direction"><?= $text['direction'] ?></td>
            <td data-label="<?= $labels['amount'] ?>" class="amount"><?= $text['amount'] ?></td>
            <td data-label="<?= $labels['remarks'] ?>" class="remarks"><?= $text['remarks'] ?></td>
            <td data-label="<?= $labels['balance'] ?>" class="total"><?= $text['balance'] ?></td>
        </tr>
<?php
    }
}
