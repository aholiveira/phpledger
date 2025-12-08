<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\Html;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;

final class AccountFormView
{
    private ApplicationObjectInterface $app;
    public function render(ApplicationObjectInterface $app, array $data): void
    {
        $this->app = $app;
        $account = $data['account'];
        $back = strtolower($data['back'] ?? "");
        $lang = $data['lang'] ?? $this->app->l10n()->lang();
        $errors = $data['errors'] ?? [];
        $pagetitle = $this->app->l10n()->l('accounts');
        $types = ObjectFactory::accounttype()->getList();
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html(); ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div class="header">
                    <p style="margin:0">
                        <?php if ($back === "balances"): ?>
                            <a href="index.php?action=balances&lang=<?= htmlspecialchars($lang) ?>"><?php $this->app->l10n()->pl('Back to list'); ?></a>
                        <?php else: ?>
                            <a href="index.php?action=accounts&lang=<?= htmlspecialchars($lang) ?>"><?php $this->app->l10n()->pl('Back to list'); ?></a>
                        <?php endif ?>
                    </p>
                </div>
                <main>
                    <div class="main config single_item account_type_form" id="main">
                        <form method="POST" action="index.php?action=account&id=<?= (int)($account->id ?? 0) ?>">
                            <?= CSRF::inputField() ?>
                            <input type="hidden" name="action" value="account">
                            <input type="hidden" name="id" value="<?= (int)($account->id ?? 0) ?>">
                            <p>
                                <label for="name">Name</label>
                                <input id="name" name="name" value="<?= htmlspecialchars($account->name ?? '') ?>">
                            </p>
                            <?php if (in_array('name', $errors, true)): ?>
                                <p style="color:red">Name required</p>
                            <?php endif; ?>
                            <p>
                                <label for="number">Number</label>
                                <input id="number" name="number" value="<?= htmlspecialchars($account->number ?? '') ?>">
                            </p>
                            <p>
                                <label for="typeId">Type</label>
                                <select id="typeId" name="typeId">
                                    <option value="0"></option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= (int)$t->id ?>" <?= (isset($account->typeId) && $account->typeId == $t->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t->description) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p>
                                <label for="iban">IBAN</label>
                                <input id="iban" name="iban" width="100" value="<?= htmlspecialchars($account->iban ?? '') ?>">
                            </p>
                            <p>
                                <label for="swift">SWIFT</label>
                                <input id="swift" name="swift" value="<?= htmlspecialchars($account->swift ?? '') ?>">
                            </p>
                            <p>
                                <label for="openDate">Open date</label>
                                <input id="openDate" type="date" name="openDate" value="<?= htmlspecialchars($account->openDate ?? date('Y-m-d')) ?>">
                            </p>
                            <p>
                                <label for="closeDate">Close date</label>
                                <input id="closeDate" type="date" name="closeDate" value="<?= htmlspecialchars($account->closeDate ?? '') ?>">
                            </p>
                            <p>
                                <label for="activa">Active</label>
                                <input id="activa" type="checkbox" name="activa" <?= !empty($account->activa) ? 'checked' : '' ?>>
                            </p>
                            <p><button type="submit">Save</button></p>
                            <?php if (in_array('other', $errors, true)): ?>
                                <p style="color:red">Check your data</p>
                            <?php endif; ?>
                        </form>
                    </div>
                </main>
                <?php Html::footer($this->app, $data['action']); ?>
            </div>
        </body>

        </html>
<?php
    }
}
