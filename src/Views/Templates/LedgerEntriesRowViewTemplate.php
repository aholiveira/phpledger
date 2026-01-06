<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

final class LedgerEntriesRowViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <tr id="<?= $text['id'] ?>">
            <td lang="<?= $lang ?>" data-label="<?= $label['edit'] ?>" class="id"><a title="<?= $title['editlink'] ?>" href="<?= $href['editlink'] ?>"><?= $label['edit'] ?></a></td>
            <td lang="<?= $lang ?>" data-label="<?= $label['id'] ?>" class="id"><?= $text['id'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $label['date'] ?>" class="data"><?= $text['date'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $label['category'] ?>" class="category"><a title="<?= $title['category'] ?>" href="<?= $href['category'] ?>"><?= $text['category'] ?></a></td>
            <td lang="<?= $lang ?>" data-label="<?= $label['currency'] ?>" class="currency"><?= $text['currency'] ?></td>
            <td lang="<?= $lang ?>" data-label="<?= $label['account'] ?>" class="account"><a title="<?= $title['account'] ?>" href="<?= $href['account'] ?>"><?= $text['account'] ?></a></td>
            <td data-label="<?= $label['dc'] ?>" class="direction"><?= $text['direction'] ?></td>
            <td data-label="<?= $label['amount'] ?>" class="amount"><?= $text['amount'] ?></td>
            <td data-label="<?= $label['remarks'] ?>" class="remarks"><?= $text['remarks'] ?></td>
            <td data-label="<?= $label['balance'] ?>" class="total"><?= $text['balance'] ?></td>
        </tr>
<?php
    }
}
