<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Domain\AccountType;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;

class AccountTypeFormView
{
    private ApplicationObjectInterface $app;
    public function render(ApplicationObjectInterface $app, AccountType $object, ?string $message, string $action): void
    {
        $this->app = $app;
        $pagetitle = "Tipo de contas";
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid" id="maingrid">
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div class="header" style="height: 0;"></div>
                <div class="main config" id="main">
                    <?php if (!empty($message)): ?>
                        <p><?= htmlspecialchars($message); ?></p>
                    <?php endif ?>
                    <form method="POST" action="index.php?action=account_type&id=<?= $object->id ?>">
                        <input type="hidden" name="action" value="account_type">
                        <?= CSRF::inputField() ?>
                        <p>
                            <label for="id">ID</label>
                            <input type="text" readonly size="4" id="id" name="id" value="<?= $object->id ?>">
                        </p>
                        <p>
                            <label for="description">Descri&ccedil;&atilde;o</label>
                            <input type="text" size="30" maxlength="30" name="description" value="<?= $object->description ?>">
                        </p>
                        <p>
                            <label for="savings">Poupan&ccedil;a</label>
                            <input type="checkbox" id="savings" name="savings" <?= $object->savings ? "checked" : "" ?>>
                        </p>
                        <p>
                            <span style="grid-column: 2/2;">
                                <input type="submit" name="update" value="Gravar">
                                <input type="submit" name="update" value="Apagar" onclick="return confirm('Pretende apagar o registo?');">
                            </span>
                        </p>
                    </form>
                </div>
                <?php Html::footer($this->app, $action); ?>
            </div>
        </body>

        </html>
<?php
    }
}
