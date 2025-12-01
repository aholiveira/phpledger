<?php

namespace PHPLedger\Views;

use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;

final class AccountFormView
{
    /**
     * Render the add/edit form.
     *
     * @param array $data
     * @return void
     */
    public function render(array $data): void
    {
        $account = $data['account'];
        $lang = $data['lang'] ?? L10n::$lang;
        $errors = $data['errors'] ?? [];
        $pagetitle = L10n::l('accounts');
        $types = ObjectFactory::accounttype()->getList();
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html(); ?>">

        <head><?php Html::header($pagetitle); ?></head>

        <body>
            <div class="maingrid">
                <?php Html::menu(); ?>
                <div class="header">
                    <p style="margin:0">
                        <a href="index.php?action=accounts&lang=<?= $lang ?>"><?= L10n::l('Back to list') ?></a>
                    </p>
                </div>
                <main>
                    <div class="main config single_item account_type_form" id="main">
                        <form method="POST" action="index.php?action=account">
                            <?= CSRF::inputField() ?>
                            <input type="hidden" name="id" value="<?= (int)($account->id ?? 0) ?>">
                            <p><label>Name</label><input name="name" value="<?= htmlspecialchars($account->name ?? '') ?>"></p>
                            <?php if (in_array('name', $errors, true)): ?><p style="color:red">Name required</p><?php endif; ?>
                            <p><label>Number</label><input name="number" value="<?= htmlspecialchars($account->number ?? '') ?>"></p>
                            <p><label>Type</label>
                                <select name="typeId">
                                    <option value="0"></option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= (int)$t->id ?>" <?= (isset($account->typeId) && $account->typeId == $t->id) ? 'selected' : '' ?>><?= htmlspecialchars($t->description) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p><label>IBAN</label><input name="iban" width="100" value="<?= htmlspecialchars($account->iban ?? '') ?>"></p>
                            <p><label>SWIFT</label><input name="swift" value="<?= htmlspecialchars($account->swift ?? '') ?>"></p>
                            <p><label>Open date</label><input type="date" name="openDate" value="<?= htmlspecialchars($account->openDate ?? date('Y-m-d')) ?>"></p>
                            <p><label>Close date</label><input type="date" name="closeDate" value="<?= htmlspecialchars($account->closeDate ?? '') ?>"></p>
                            <p><label>Active</label><input type="checkbox" name="activa" <?= !empty($account->activa) ? 'checked' : '' ?>></p>
                            <p><button type="submit">Save</button></p>
                            <?php if (in_array('other', $errors, true)): ?><p style="color:red">Check your data</p><?php endif; ?>
                        </form>
                    </div>
                </main>
                <?php Html::footer(); ?>
            </div>
        </body>

        </html>
<?php
    }
}
