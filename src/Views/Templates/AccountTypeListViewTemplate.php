<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class AccountTypeListViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle, $appTitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php $ui->menu($label, $menu); ?>
                <div class="header">
                    <p style="margin:0"><a href="index.php?action=account_type&lang=<?= $lang ?>" aria-label="<?= $label["add"] ?>"><?= $label["add"] ?></a></p>
                </div>
                <div class="main" id="main">
                    <table class="lista account_type">
                        <thead>
                            <tr>
                                <th><?= $label['actions'] ?>
                                <th><?= $label['id'] ?></th>
                                <th><?= $label['description'] ?></th>
                                <th><?= $label['savings'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr id="<?= $row['id'] ?>">
                                    <td data-label="<?= $label['actions'] ?>"><a title="<?= $label['edit'] ?>" href="index.php?action=account_type&id=<?= $row['id'] ?>" aria-label="<?= $label['edit'] ?>"><?= $label['edit'] ?></a></td>
                                    <td data-label="<?= $label['id'] ?>"><?= $row['id'] ?></td>
                                    <td data-label="<?= $label['description'] ?>"><?= $row['description'] ?></td>
                                    <td class="active" data-label="<?= $label['savings'] ?>"><?= $row['savings'] ? '✓' : '–' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

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
