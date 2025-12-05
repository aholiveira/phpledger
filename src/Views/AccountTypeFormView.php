<?php

namespace PHPLedger\Views;

use PHPLedger\Domain\AccountType;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class AccountTypeFormView
{
    public function render(AccountType $object, ?string $message): void
    {
        $pagetitle = "Tipo de contas";
?>
        <!DOCTYPE html>
        <html lang="<?= l10n::html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?= Html::header() ?>
        </head>

        <body>
            <div class="maingrid" id="maingrid">
                <?php Html::menu(); ?>
                <div class="header" style="height: 0;"></div>
                <div id="main" class="main">
                    <?php if (!empty($message)): ?>
                        <p><?= htmlspecialchars($message); ?></p>
                    <?php endif ?>
                    <form method="POST">
                        <?= CSRF::inputField() ?>
                        <table class="single_item account_type_form">
                            <tr>
                                <td>ID</td>
                                <td data-label="ID">
                                    <input type="text" readonly size="4" name="id" value="<?= $object->id ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>Descri&ccedil;&atilde;o</td>
                                <td data-label="Descri&ccedil;&atilde;o">
                                    <input type="text" size="30" maxlength="30" name="description"
                                        value="<?= $object->description ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>Poupan&ccedil;a</td>
                                <td data-label="Poupan&ccedil;a">
                                    <input type="checkbox" name="savings" <?= $object->savings ? "checked" : "" ?>>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="submit" name="update" value="Gravar"></td>
                                <td><input type="submit" name="update" value="Apagar"
                                        onclick="return confirm('Pretende apagar o registo?');"></td>
                            </tr>
                        </table>
                    </form>
                </div>
                <?php Html::footer(); ?>
            </div>
        </body>

        </html>
<?php
    }
}
