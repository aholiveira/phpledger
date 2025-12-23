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
            <title><?= Html::title($pagetitle, $appTitle) ?></title>
            <?php Html::header() ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php $ui->menu($label, $menu); ?>
                <div class="header" style="height: 0;"></div>
                <div class="main" id="main">
                    <div class="saldos">
                        <table class="lista saldos">
                            <thead>
                                <tr>
                                    <th><?= $label['account'] ?></th>
                                    <th><?= $label['deposits'] ?></th>
                                    <th><?= $label['withdrawals'] ?></th>
                                    <th><?= $label['balance'] ?></th>
                                    <th><?= $label['percent'] ?></th>
                                    <th><?= $label['entries'] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows as $row) {
                                ?>
                                    <tr>
                                        <td class="account" data-label="<?= $label['account'] ?>"><?= !empty($row['href']['name']) ? "<a title=\"{$label['edit_account']}\" href=\"{$row['href']['name']}\">" : "" ?><?= $row['text']['name'] ?><?= !empty($row['href']['name']) ? "</a>" : "" ?></td>
                                        <?php foreach (["deposits", "withdrawals", "balance", "percent"] as $r): ?>
                                            <td class="<?= $r ?>" data-label="<?= $label[$r] ?>"><?= $row['text'][$r] ?></td>
                                        <?php endforeach; ?>
                                        <td class="entries-list" data-label="<?= $label['entries'] ?>"><?php if (!empty($row['href']['entries'])): ?><a title="<?= $label['account_entries'] ?>" href="<?= $row['href']['entries'] ?>"><?= $label['list'] ?></a><?php endif; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php $ui->footer($label, $footer); ?>
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
