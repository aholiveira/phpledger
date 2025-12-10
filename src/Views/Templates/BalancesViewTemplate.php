<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class BalancesViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($title) ?></title>
            <?php Html::header() ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php Html::menu($app->l10n(), $isAdmin); ?>
                <div class="header" style="height: 0;"></div>
                <div class="main" id="main">
                    <div class="saldos">
                        <table class="lista saldos">
                            <thead>
                                <tr>
                                    <th><?= $l10n['account'] ?></th>
                                    <th><?= $l10n['deposits'] ?></th>
                                    <th><?= $l10n['withdrawals'] ?></th>
                                    <th><?= $l10n['balance'] ?></th>
                                    <th><?= $l10n['percent'] ?></th>
                                    <th><?= $l10n['entries'] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows as $row):
                                ?>
                                    <tr>
                                        <td class="account" data-label="<?= $l10n['account'] ?>">
                                            <?= !empty($row['href']['name']) ? "<a title=\"{$l10n['edit_account']}\" href=\"{$row['href']['name']}\">" : "" ?>
                                            <?= $row['text']['name'] ?>
                                            <?= !empty($row['href']['name']) ? "</a>" : "" ?>
                                        </td>
                                        <?php foreach (["deposits", "withdrawals", "balance", "percent"] as $r): ?>
                                            <td class="<?= $r ?>" data-label="<?= $l10n[$r] ?>"><?= $row['text'][$r] ?></td>
                                        <?php endforeach; ?>
                                        <td class="entries-list" data-label="<?= $l10n['entries'] ?>">
                                            <?php if (!empty($row['href']['entries'])): ?>
                                                <a title="<?= $l10n['account_entries'] ?>" href="<?= $row['href']['entries'] ?>"><?= $l10n['list'] ?></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php Html::footer($app, $action); ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById("preloader").style.display = "none";
                }, 0);
            </script>
        </body>

        </html>
<?php
    }
}
