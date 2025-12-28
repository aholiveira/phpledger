<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;

final class ReportViewTemplate extends AbstractViewTemplate
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
            <script src="assets/common.js"></script>
            <script>
                function toggleGroup(id) {
                    const head = document.getElementById('group-head-' + id);
                    const expanded = head.dataset.expanded === '1';

                    // Toggle child rows
                    document.querySelectorAll('.group-child-' + id).forEach(r => {
                        r.style.display = expanded ? 'none' : '';
                    });

                    // Toggle top-level column values
                    document.querySelectorAll('.group-col-' + id).forEach(cell => {
                        const val = expanded ? cell.dataset.collapsed : cell.dataset.direct;
                        const span = cell.querySelector('span');
                        if (span) {
                            span.textContent = val;
                        } else {
                            cell.textContent = val;
                        }
                    });

                    // Toggle total and average
                    ['total', 'avg'].forEach(t => {
                        const el = document.getElementById(`group-${t}-${id}`);
                        const val = expanded ? el.dataset.collapsed : el.dataset.direct;
                        const span = el.querySelector('span');
                        if (span) {
                            span.textContent = val;
                        } else {
                            el.textContent = val;
                        }
                    });

                    // Update expanded state
                    head.dataset.expanded = expanded ? '0' : '1';

                    // Update toggle button text (+/-)
                    const toggleBtn = head.querySelector('.toggle-btn');
                    if (toggleBtn) {
                        toggleBtn.textContent = expanded ? '+' : '-';
                    }

                    stripeTable();
                }

                function stripeTable() {
                    const rows = document.querySelectorAll('#report tbody tr');
                    let visibleIndex = 0;
                    rows.forEach(r => {
                        if (r.style.display === 'none') return;
                        r.classList.remove('row-even', 'row-odd');
                        r.classList.add(visibleIndex % 2 === 0 ? 'row-even' : 'row-odd');
                        visibleIndex++;
                    });
                }

                document.addEventListener('DOMContentLoaded', stripeTable);
            </script>
        </head>

        <body>
            <div class="maingrid">
                <?php $ui->menu($label, $menu); ?>
                <div id="header" class="header main config">
                    <?php $reportViewFormTemplate->render(compact(
                        'periodOptions',
                        'filterFields',
                        'period',
                        'lang',
                        'label',
                    )); ?>
                </div>
                <div class="main" id="main">
                    <div class="main-container">
                        <div class="csv-download">
                            <a href="<?= htmlspecialchars($downloadUrl) ?>"><small><?= $label['download_data'] ?><img src="assets/file-csv-solid-full.svg" alt="CSV"></small></a>
                            <a href="<?= htmlspecialchars($downloadRawUrl) ?>"><small><?= $label['download_raw_data'] ?><img src="assets/file-csv-solid-full.svg" alt="CSV"></small></a>
                        </div>
                        <div class="table-wrapper">
                            <?php $reportViewTableTemplate->render(compact(
                                'label',
                                'reportData',
                                'columnLabels',
                                'reportViewTableTopLevelTemplate',
                                'reportViewTableChildRowTemplate',
                            )); ?>
                        </div>
                    </div>
                </div>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>

        </html>
<?php
    }
}
