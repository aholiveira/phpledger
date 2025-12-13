<?php

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
            <title><?= Html::title($pagetitle) ?></title>
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
                        <a href="index.php?action=account&lang=<?= htmlspecialchars($lang) ?>">
                            <?= $label['add'] ?>
                        </a>
                    </p>
                </div>
                <div class="main" id="main">
                    <table class="lista contas account">
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
                                    <td data-label="<?= $label['id'] ?>"><?= htmlspecialchars($row['id']) ?></td>
                                    <td data-label="<?= $label['name'] ?>"><?= htmlspecialchars($row['name']) ?></td>
                                    <td data-label="<?= $label['number'] ?>"><?= htmlspecialchars($row['number']) ?></td>
                                    <td data-label="<?= $label['type'] ?>"><?= htmlspecialchars($row['type']) ?></td>
                                    <td data-label="<?= $label['iban'] ?>"><?= htmlspecialchars($row['iban']) ?></td>
                                    <td data-label="<?= $label['swift'] ?>"><?= htmlspecialchars($row['swift']) ?></td>
                                    <td data-label="<?= $label['open'] ?>"><?= htmlspecialchars($row['openDate']) ?></td>
                                    <td data-label="<?= $label['close'] ?>"><?= htmlspecialchars($row['closeDate']) ?></td>
                                    <td class="active" data-label="<?= $label['active'] ?>"><?= $row['activa'] ? '✓' : '–' ?></td>
                                    <td data-label="<?= $label['actions'] ?>"><a href="index.php?action=account&id=<?= (int)$row['id'] ?>&lang=<?= htmlspecialchars($lang) ?>"><?= $label['edit'] ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
