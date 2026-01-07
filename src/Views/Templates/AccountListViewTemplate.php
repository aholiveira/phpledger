<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class AccountListViewTemplate extends AbstractViewTemplate
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
                    <p style="margin:0">
                        <a href="index.php?action=account&lang=<?= $this->htmlSafe($lang) ?>" aria-label="<?= $label['add'] ?>"><?= $label['add'] ?></a>
                    </p>
                </div>
                <div class="main" id="main">
                    <div class="entry_category_list">
                        <table class="lista entry_category">
                            <thead>
                                <tr>
                                    <th><?= $label['id'] ?></th>
                                    <th><?= $label['name'] ?></th>
                                    <th><?= $label['number'] ?></th>
                                    <th><?= $label['type'] ?></th>
                                    <th><?= $label['iban'] ?></th>
                                    <th><?= $label['swift'] ?></th>
                                    <th><?= $label['open'] ?></th>
                                    <th><?= $label['close'] ?></th>
                                    <th><?= $label['active'] ?></th>
                                    <th><?= $label['actions'] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($list as $row): ?>
                                    <tr>
                                        <td data-label="<?= $label['id'] ?>"><?= $this->htmlSafe($row['id']) ?></td>
                                        <td data-label="<?= $label['name'] ?>"><?= $this->htmlSafe($row['name']) ?></td>
                                        <td data-label="<?= $label['number'] ?>"><?= $this->htmlSafe($row['number']) ?></td>
                                        <td data-label="<?= $label['type'] ?>"><?= $this->htmlSafe($row['type']) ?></td>
                                        <td data-label="<?= $label['iban'] ?>"><?= $this->htmlSafe($row['iban']) ?></td>
                                        <td data-label="<?= $label['swift'] ?>"><?= $this->htmlSafe($row['swift']) ?></td>
                                        <td data-label="<?= $label['open'] ?>"><?= $this->htmlSafe($row['openDate']) ?></td>
                                        <td data-label="<?= $label['close'] ?>"><?= $this->htmlSafe($row['closeDate']) ?></td>
                                        <td class="active" data-label="<?= $label['active'] ?>"><?= $row['active'] ? '✓' : '–' ?></td>
                                        <td data-label="<?= $label['actions'] ?>"><a href="index.php?action=account&id=<?= (int)$row['id'] ?>&lang=<?= $this->htmlSafe($lang) ?>" aria-label="<?= $label['edit'] ?>"><?= $label['edit'] ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php $ui->footer($label, $footer); ?>
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
