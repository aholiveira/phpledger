<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Util\CSRF;
use PHPLedger\Views\Templates\AbstractViewTemplate;

final class AccountFormViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div class="header">
                    <p style="margin:0">
                        <?php if ($back === "balances"): ?>
                            <a href="index.php?action=balances&lang=<?= htmlspecialchars($lang) ?>" aria-label="<?= $label['back_to_balances'] ?>"><?= $label['back_to_balances'] ?></a>
                        <?php else: ?>
                            <a href="index.php?action=accounts&lang=<?= htmlspecialchars($lang) ?>" aria-label="<?= $label['back_to_list'] ?>"><?= $label['back_to_list'] ?></a>
                        <?php endif ?>
                    </p>
                </div>
                <main>
                    <div class="main config single_item account_type_form" id="main">
                        <form method="POST" action="index.php?action=account&id=<?= ($text['id']) ?>">
                            <?= $csrf ?>
                            <input type="hidden" name="action" value="account">
                            <input type="hidden" name="lang" value="<?= $lang ?>">
                            <p>
                                <label for="id"><?= $label['id'] ?></label>
                                <input id="id" name="id" value="<?= htmlspecialchars($text['id']) ?>" readonly>
                            </p>
                            <p>
                                <label for="name"><?= $label['name'] ?></label>
                                <input id="name" name="name" value="<?= htmlspecialchars($text['name']) ?>" required="">
                            </p>
                            <?php if (in_array('name', $errors, true)): ?>
                                <p style="color:red"><?= $label['name_required'] ?></p>
                            <?php endif; ?>
                            <p>
                                <label for="number"><?= $label['number'] ?></label>
                                <input id="number" name="number" value="<?= htmlspecialchars($text['number']) ?>">
                            </p>
                            <p>
                                <label for="typeId"><?= $label['type'] ?></label>
                                <select id="typeId" name="typeId" required="">
                                    <?php $this->renderSelectOptions($accountTypes); ?>
                                </select>
                            </p>
                            <p>
                                <label for="iban"><?= $label['iban'] ?></label>
                                <input id="iban" name="iban" width="100" value="<?= htmlspecialchars($text['iban']) ?>">
                            </p>
                            <p>
                                <label for="swift"><?= $label['swift'] ?></label>
                                <input id="swift" name="swift" value="<?= htmlspecialchars($text['swift']) ?>">
                            </p>
                            <p>
                                <label for="openDate"><?= $label['openDate'] ?></label>
                                <input id="openDate" type="date" name="openDate" value="<?= $text['openDate'] ?>">
                            </p>
                            <p>
                                <label for="closeDate"><?= $label['closeDate'] ?></label>
                                <input id="closeDate" type="date" name="closeDate" value="<?= $text['closeDate'] ?>">
                            </p>
                            <p>
                                <label for="active"><?= $label['active'] ?></label>
                                <input id="active" type="checkbox" name="active" <?= $text['active'] ? 'checked' : '' ?>>
                            </p>
                            <p>
                                <button style="grid-column: 2 / 2;" type="submit" name="itemaction" value="save"><?= $label['save'] ?></button>
                                <button style="grid-column: 2 / 2;" type="submit" name="itemaction" value="delete" onclick="return confirm('Delete this account?');"><?= $label['delete'] ?></button>
                            </p>
                            <?php if (in_array('other', $errors, true)): ?>
                                <p style="color:red"><?= $label['check_your_data'] ?></p>
                            <?php endif; ?>
                        </form>
                    </div>
                </main>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>

        </html>
<?php
    }
}
