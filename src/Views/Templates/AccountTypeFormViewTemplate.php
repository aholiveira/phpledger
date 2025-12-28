<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class AccountTypeFormViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
        $pagetitle = "Tipo de contas";
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
                    <a href="index.php?action=account_types&lang=<?= $lang ?>" aria-label="<?= $label['back_to_list'] ?>"><?= $label['back_to_list'] ?></a>
                </div>
                <div class="main config" id="main">
                    <?php $ui->notification($notification, $success) ?>
                    <form method="POST" action="index.php?action=account_type&id=<?= $row['id'] ?>">
                        <input type="hidden" name="action" value="account_type">
                        <input type="hidden" name="lang" value="<?= $lang ?>">
                        <?= $csrf ?>
                        <p>
                            <label for="id"><?= $label['id'] ?></label>
                            <input type="text" readonly size="4" id="id" name="id" value="<?= $row['id'] ?>">
                        </p>
                        <p>
                            <label for="description"><?= $label['description'] ?></label>
                            <input type="text" size="30" maxlength="30" name="description" value="<?= $row['description'] ?>">
                        </p>
                        <p>
                            <label for="savings"><?= $label['savings'] ?></label>
                            <input type="checkbox" id="savings" name="savings" <?= $row['savings'] ? "checked" : "" ?>>
                        </p>
                        <p>
                            <span style="grid-column: 2 / 2;">
                                <button type="submit" name="update" value="save"><?= $label['save'] ?></button>
                                <button type="submit" name="update" value="delete" onclick="return confirm('<?= $label['are_you_sure_you_want_to_delete'] ?>');"><?= $label['delete'] ?></button>
                            </span>
                        </p>
                    </form>
                </div>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>

        </html>
<?php
    }
}
