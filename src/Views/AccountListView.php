<?php

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\Html;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;

final class AccountListView
{
    private ApplicationObjectInterface $app;
    /**
     * Render account list page.
     *
     * @param array $data
     * @return void
     */
    public function render(ApplicationObjectInterface $app, array $data): void
    {
        $this->app = $app;
        $list = $data['list'] ?? [];
        $lang = htmlspecialchars($data['lang'] ?? $this->app->l10n()->lang());
        $pagetitle = $this->app->l10n()->l('account_types');
        $cacheTypeId = [];
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html(); ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu($this->app->l10n(), $this->app->session()->get('isAdmin', false)); ?>
                <div class="header">
                    <p style="margin:0"><a href="index.php?action=account&lang=<?= $lang ?>"><?php $this->app->l10n()->pl('add'); ?></a></p>
                </div>
                <div class="main" id="main">
                    <table class="lista contas account">
                        <thead>
                            <tr>
                                <th><?= $this->app->l10n()->l("id") ?></th>
                                <th><?= $this->app->l10n()->l("name") ?></th>
                                <th><?= $this->app->l10n()->l("number") ?></th>
                                <th><?= $this->app->l10n()->l("type") ?></th>
                                <th><?= $this->app->l10n()->l("iban") ?></th>
                                <th><?= $this->app->l10n()->l("swift") ?></th>
                                <th><?= $this->app->l10n()->l("open") ?></th>
                                <th><?= $this->app->l10n()->l("close") ?></th>
                                <th><?= $this->app->l10n()->l("active") ?></th>
                                <th><?= $this->app->l10n()->l("actions") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $obj): ?>
                                <tr>
                                    <td data-label="<?= $this->app->l10n()->l("id") ?>"><a href="index.php?action=account&id=<?= (int)($obj->id ?? 0) ?>&lang=<?= $lang ?>"><?php echo htmlspecialchars($obj->id ?? ''); ?></a></td>
                                    <td data-label="<?= $this->app->l10n()->l("name") ?>"><?= htmlspecialchars($obj->name ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("number") ?>"><?= htmlspecialchars($obj->number ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("type") ?>">
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
                                    <td data-label="<?= $this->app->l10n()->l("iban") ?>"><?= htmlspecialchars($obj->iban ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("swift") ?>"><?= htmlspecialchars($obj->swift ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("open") ?>"><?= htmlspecialchars($obj->openDate ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("close") ?>"><?= htmlspecialchars($obj->closeDate ?? '') ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("active") ?>"><?= !empty($obj->activa) ? $this->app->l10n()->l("yes") : $this->app->l10n()->l("no") ?></td>
                                    <td data-label="<?= $this->app->l10n()->l("actions") ?>">
                                        <form method="POST" action="index.php?action=account" style="display:inline" onsubmit="return confirm('Delete this account?');">
                                            <?= CSRF::inputField() ?>
                                            <input type="hidden" name="action" value="account">
                                            <input type="hidden" name="itemaction" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)($obj->id ?? 0) ?>">
                                            <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;padding:0"><?= $this->app->l10n()->l("delete") ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php Html::footer($this->app, $data['action']); ?>
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
