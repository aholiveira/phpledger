<?php

namespace PHPLedger\Views\Templates;

final class ReportViewFormTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <form name="filtro" method="GET">
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <?php foreach ($filterFields as $f): ?>
                <p><label for="<?= $f['id'] ?>"><?= $f['label'] ?></label><input type="<?= $f['type'] ?>" id="<?= $f['id'] ?>" name="<?= $f['id'] ?>" maxlength="4" size="6" value="<?= $f['value'] ?>"></p>
            <?php endforeach ?>
            <p><label for="period"><?= $label['period'] ?></label>
                <select name="period" id="period">
                    <?php $this->renderSelectOptions($periodOptions) ?>
                </select>
            </p>
            <p>
                <span style="grid-column: 2 / 2;">
                    <button type="submit" value="subaction" value="calculate"><?= $label['calculate'] ?></button>
                </span>
            </p>
        </form>
<?php
    }
}
