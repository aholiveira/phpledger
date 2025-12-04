<?php

namespace PHPLedger\Views;

use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;

final class AccountListView
{
    /**
     * Render account list page.
     *
     * @param array $data
     * @return void
     */
    public function render(array $data): void
    {
        $list = $data['list'] ?? [];
        $lang = htmlspecialchars($data['lang'] ?? L10n::$lang);
        $pagetitle = L10n::l('account_types');
        $cacheTypeId = [];
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html(); ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu(); ?>
                <div class="header">
                    <p style="margin:0"><a href="index.php?action=account&lang=<?= $lang ?>"><?php L10n::pl('add'); ?></a></p>
                </div>
                <div class="main" id="main">
                    <table class="lista contas account">
                        <thead>
                            <tr>
                                <th><?= L10n::l("id") ?></th>
                                <th><?= L10n::l("name") ?></th>
                                <th><?= L10n::l("number") ?></th>
                                <th><?= L10n::l("type") ?></th>
                                <th><?= L10n::l("iban") ?></th>
                                <th><?= L10n::l("swift") ?></th>
                                <th><?= L10n::l("open") ?></th>
                                <th><?= L10n::l("close") ?></th>
                                <th><?= L10n::l("active") ?></th>
                                <th><?= L10n::l("actions") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $obj): ?>
                                <tr>
                                    <td data-label="<?= L10n::l("id") ?>"><a href="index.php?action=account&id=<?= (int)($obj->id ?? 0) ?>&lang=<?= $lang ?>"><?php echo htmlspecialchars($obj->id ?? ''); ?></a></td>
                                    <td data-label="<?= L10n::l("name") ?>"><?= htmlspecialchars($obj->name ?? '') ?></td>
                                    <td data-label="<?= L10n::l("number") ?>"><?= htmlspecialchars($obj->number ?? '') ?></td>
                                    <td data-label="<?= L10n::l("type") ?>">
                                        <?php
                                        $type = '';
                                        if (!empty($obj->typeId)) {
                                            if (!array_key_exists($obj->typeId, $cacheTypeId)) {
                                                $t = ObjectFactory::accountType()::getById($obj->typeId);
                                                $cacheTypeId[$obj->typeId] = $t->description ?? '';
                                            }
                                            $type = $cacheTypeId[$obj->typeId];
                                        }
                                        echo htmlspecialchars($type);
                                        ?>
                                    </td>
                                    <td data-label="<?= L10n::l("iban") ?>"><?= htmlspecialchars($obj->iban ?? '') ?></td>
                                    <td data-label="<?= L10n::l("swift") ?>"><?= htmlspecialchars($obj->swift ?? '') ?></td>
                                    <td data-label="<?= L10n::l("open") ?>"><?= htmlspecialchars($obj->openDate ?? '') ?></td>
                                    <td data-label="<?= L10n::l("close") ?>"><?= htmlspecialchars($obj->closeDate ?? '') ?></td>
                                    <td data-label="<?= L10n::l("active") ?>"><?= !empty($obj->activa) ? L10n::l("yes") : L10n::l("no") ?></td>
                                    <td data-label="<?= L10n::l("actions") ?>">
                                        <form method="POST" action="index.php?action=account" style="display:inline" onsubmit="return confirm('Delete this account?');">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)($obj->id ?? 0) ?>">
                                            <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;padding:0"><?= L10n::l("delete") ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php Html::footer(); ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('preloader').style.display = 'none';
                }, 0);
            </script>
        </body>

        </html>
<?php
    }
}
