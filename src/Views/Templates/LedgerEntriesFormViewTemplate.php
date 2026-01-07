<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

final class LedgerEntriesFormViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <?php if ($formData['id'] === 0) {
        ?>
            <tfoot>
            <?php
        }
            ?>
            <tr>
                <td data-label="<?= $label['actions'] ?>"><?= $csrf ?><input type="hidden" name="lang" value="<?= $lang ?>" /><input type="hidden" name="action" value="ledger_entries" /><input type="hidden" name="id" value="<?= $formData['id'] ?>"><button class="submit" type="submit" name="save" value="save"><?= $label['save'] ?></button></td>
                <td data-label="<?= $label['id'] ?>"><?= $formData['id'] === 0 ? '-' : $formData['id'] ?></td>
                <td data-label="<?= $label['date'] ?>" class="id"><input type="date" id="data_mov" name="data_mov" required value="<?= $formData['date'] ?>"></td>
                <td data-label="<?= $label['category'] ?>" class="category"><select name="categoryId"><?php $this->renderSelectOptions($formData['entryCategoryRows']) ?></select></td>
                <td data-label="<?= $label['currency'] ?>" class="currency"><select name="currencyId"><?php $this->renderSelectOptions($formData['currencyRows']) ?></select></td>
                <td data-label="<?= $label['account'] ?>" class="account"><select name="accountId"><?php $this->renderSelectOptions($formData['accountRows']) ?></select></td>
                <td data-label="<?= $label['dc'] ?>" class="direction"><select name="direction"><?php $this->renderSelectOptions($formData['direction']) ?></select></td>
                <td data-label="<?= $label['amount'] ?>" class="amount"><input type="number" step="0.01" name="currencyAmount" placeholder="0.00" value="<?= $formData['amount'] ?>"></td>
                <td data-label="<?= $label['exchangeRate'] ?>" class="exchangeRate"><input type="number" step="0.00000001" name="exchangeRate" placeholder="0.00000000" value="<?= $formData['exchangeRate'] ?>"></td>
                <td data-label="<?= $label['euro'] ?>" class="euroAmount"><input type="number" step="0.01" name="euroAmount" placeholder="0.00" value="<?= $formData['euroAmount'] ?>"></td>
                <td data-label="<?= $label['remarks'] ?>" class="remarks"><input type="text" name="remarks" maxlength="255" value="<?= $formData['remarks'] ?>"></td>
                <td data-label="<?= $label['balance'] ?>" class="total" style="text-align: right"><?= $formData['balance'] ?></td>
            </tr>
            <?php
            if ($formData['id'] === 0) {
            ?>
            </tfoot>
        <?php
            }
        ?>
<?php
    }
}
