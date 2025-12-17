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
                <td data-label="<?= $label['actions'] ?>"><?= $csrf ?><input type="hidden" name="lang" value="<?= $lang ?>" /><input type="hidden" name="action" value="ledger_entries" /><input type="hidden" name="filters" value='<?= !empty($filters) ? htmlspecialchars(json_encode($filters), ENT_QUOTES) : ""; ?>'><input type="hidden" name="id" value="<?= $formData['id'] ?>"><button class="submit" type="submit" name="save" value="save"><?= $label['save'] ?></button></td>
                <td data-label="<?= $label['id'] ?>"><?= $formData['id'] === 0 ? '-' : $formData['id'] ?></td>
                <td data-label="<?= $label['date'] ?>" class="id"><select class="date-fallback" style="display: none" name="data_movAA"><?= Html::yearOptions(substr($formData['date'], 0, 4)) ?></select><select class="date-fallback" style="display: none" name="data_movMM"><?= Html::monthOptions(substr($formData['date'], 5, 2)) ?></select><select class="date-fallback" style="display: none" name="data_movDD"><?= Html::dayOptions(substr($formData['date'], 8, 2)) ?></select><input class="date-fallback" type="date" id="data_mov" name="data_mov" required value="<?= $formData['date'] ?>"></td>
                <td data-label="<?= $label['category'] ?>" class="category"><select name="categoryId"><?php $this->renderSelectOptions($formData['entryCategoryRows']) ?></select></td>
                <td data-label="<?= $label['currency'] ?>" class="currency"><select name="currencyId"><?php $this->renderSelectOptions($formData['currencyRows']) ?></select></td>
                <td data-label="<?= $label['account'] ?>" class="account"><select name="accountId"><?php $this->renderSelectOptions($formData['accountRows']) ?></select></td>
                <td data-label="<?= $label['dc'] ?>" class="direction"><select name="direction"><?php $this->renderSelectOptions($formData['direction']) ?></select></td>
                <td data-label="<?= $label['amount'] ?>" class="amount"><input type="number" step="0.01" name="currencyAmount" placeholder="0.00" value="<?= $formData['amount'] ?>"></td>
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
