<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;

final class EntryCategoryFormViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<? $lang ?>">

        <head>
            <title><?= Html::title($title) ?></title>
            <?php Html::header() ?>
        </head>

        <body>
            <div class="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div class="header" style="height: 0;"></div>
                <div id="main" class="main config">
                    <form method="POST" action="index.php?action=entry_types">
                        <input type="hidden" name="action" value="entry_types">
                        <?= CSRF::inputField() ?>
                        <p><label for="id"><?= $label['id'] ?></label><input type="text" readonly size="4" name="id" value="<?= $text['id'] ?>"></p>
                        <p><label for="parentId"><?= $label['category'] ?></label>
                            <select name="parentId">
                                <?= $this->renderSelectOptions($parentRows['rows']) ?>
                            </select>
                        </p>
                        <p>
                            <label for="description"><?= $label['description'] ?></label>
                            <input type=text size=30 maxlength=30 name="description" value="<?= $text['description'] ?>">
                        </p>
                        <p>
                            <label for="active"><?= $label['active'] ?></label>
                            <input type="checkbox" name="active" <?= $text['active'] ?>>
                        </p>
                        <p>
                            <span style="grid-column: 2 / 2;">
                                <button type="submit" name="update" value="save"><?= $label['save'] ?></button>
                                <button type="submit" name="update" value="delete" onclick="return confirm('<?= $label['are_you_sure_you_want_to_delete'] ?>');"><?= $label['delete'] ?></button>
                            </span>
                    </form>
                </div>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>

        </html>

<?php
    }
}
