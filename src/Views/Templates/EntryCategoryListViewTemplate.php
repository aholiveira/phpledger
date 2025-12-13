<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class EntryCategoryListViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($title) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <?php $ui->notification($message, $success); ?>
            <div id="maingrid" class="maingrid">
                <div id="preloader">
                    <div class="spinner"></div>
                </div>
                <?php $ui->menu($label, $menu); ?>
                <div class="header">
                    <p style="margin:0"><a href="index.php?action=entry_type"><?= $label['add'] ?></a></p>
                </div>
                <div id="main" class="main">
                    <div class="entry_category_list">
                        <table class="lista entry_category">
                            <thead>
                                <tr>
                                    <th><?= $label['id'] ?></th>
                                    <th><?= $label['description'] ?></th>
                                    <th><?= $label['amount'] ?></th>
                                    <th><?= $label['active'] ?></th>
                                    <th><?= $label['actions'] ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rows as $row):
                                    $indent = $row['parentId'] > 0 ? 1 : 0;
                                    $displayDescription = str_repeat('&nbsp;', 4 * $indent) . str_repeat('&#8594 ', $indent) . $row['description'];
                                ?>
                                    <tr>
                                        <td class="id" data-label="<?= $label['id'] ?>"><?= $row['id'] ?></td>
                                        <td class="description" data-label="<?= $label['description'] ?>"><?= $displayDescription ?></td>
                                        <td class="amount" data-label="<?= $label['amount'] ?>"><?= $row['amount'] ?></td>
                                        <td class="active" data-label="<?= $label['active'] ?>"><?= $row['active'] ? '✓' : '–' ?></td>
                                        <td class="editlink" data-label="<?= $label['edit'] ?>">
                                            <?php if (isset($row['id'], $row['href']) && $row['id'] > 0): ?>
                                                <a href="<?= $row['href'] ?>" title="<?= $label['edit_category'] ?>" aria-label="<?= $label['edit'] ?>"><?= $label['edit'] ?></a>
                                            <?php else: ?>
                                                <span class="disabled">–</span>
                                            <?php endif; ?>
                                        </td>
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
                    document.getElementById("preloader").style.display = "none";
                }, 0);
            </script>
        </body>

        </html>
<?php
    }
}
