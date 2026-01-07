<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class CurrencyFormViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle, $appTitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid" id="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div class="header">
                    <a href="index.php?action=currencies&lang=<?= $lang ?>" aria-label="<?= $label['back_to_list'] ?>"><?= $label['back_to_list'] ?></a>
                </div>
                <div class="main config" id="main">
                    <?php $ui->notification($notification, $success) ?>
                    <form method="POST" action="index.php?action=currency&id=<?= $row['id'] ?>">
                        <?= $csrf ?>
                        <input type="hidden" name="action" value="currency">
                        <input type="hidden" name="lang" value="<?= $lang ?>">
                        <label for="id"><?= $label['id'] ?></label>
                        <input type="text" readonly size="4" id="id" name="id" value="<?= $row['id'] ?>">
                        <label for="code"><?= $this->htmlSafe($label['code']) ?></label>
                        <input type="text" size="3" maxlength="3" name="code" value="<?= $this->htmlSafe($row['code']) ?>">
                        <label for="description"><?= $this->htmlSafe($label['description']) ?></label>
                        <input type="text" size="30" maxlength="30" name="description" value="<?= $this->htmlSafe($row['description']) ?>">
                        <label for="exchangeRate"><?= $this->htmlSafe($label['exchangeRate']) ?></label>
                        <input type="number" id="exchangeRate" name="exchangeRate" step="0.00000001" placeholder="1.00000000" value="<?= $row['exchangeRate'] ?>">
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
