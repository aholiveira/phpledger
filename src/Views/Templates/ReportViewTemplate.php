<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Util\NumberUtil;

final class ReportViewTemplate extends AbstractViewTemplate
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
                    <form name="filtro" method="GET">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="period" value="<?= $period ?>">
                        <input type="hidden" name="lang" value="<?= $lang ?>">
                        <?php foreach ($filterFields as $f): ?>
                            <p><label for="<?= $f['id'] ?>"><?= $f['label'] ?></label><input type="<?= $f['type'] ?>" id="<?= $f['id'] ?>" name="<?= $f['id'] ?>" maxlength="4" size="6" value="<?= $f['value'] ?>"></p>
                        <?php endforeach ?>
                        <p><label for="period"><?= $label['period'] ?></label>
                            <select name="period" id="period">
                                <?php $this->renderSelectOptions($periodOptions) ?>
                            </select>
                        </p>
                        <p><input type="submit" value="<?= $label['calculate'] ?>"></p>
                    </form>
                </div>
                <div class="main" id="main">
                    <div class="viewSelector" id="viewSelector">
                        <button type="button" onclick="toggle('graph');" alt="toggle graph">Show graph</button>
                        <button type="button" onclick="toggle('table');" alt="toggle table">Show table</button>
                    </div>
                    <div class="graph" id="graph" style="display: none; width: 99%"></div>
                    <div class="table report_month" id="table" style="display: inherit; width: 99%">
                        <table class="report report_month" id="report">
                            <thead>
                                <tr>
                                    <th colspan="2"><?= $label['category'] ?></th>
                                    <?php foreach ($columnLabels as $c): ?>
                                        <th><?= htmlspecialchars($c) ?></th>
                                    <?php endforeach ?>
                                    <th><?= $label['average'] ?></th>
                                    <th><?= $label['total'] ?></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($reportData['groups'] as $g): ?>
                                    <!-- Top-level row -->
                                    <tr id="group-head-<?= $g['id'] ?>" data-expanded="0">
                                        <td style="width: min-content; cursor:pointer" onclick="toggleGroup('<?= $g['id'] ?>')"><span class="toggle-btn">+</span></td>
                                        <td><a href="index.php?<?= $g['link'] ?>" aria-label="<?= htmlspecialchars($g['label']) ?>"><?= htmlspecialchars($g['label']) ?></a></td>
                                        <?php foreach ($reportData['columns'] as $c): ?>
                                            <td class="group-col-<?= $g['id'] ?> saldos"
                                                data-direct="<?= NumberUtil::normalize($g['direct'][$c]) ?>"
                                                data-collapsed="<?= NumberUtil::normalize($g['collapsedValues'][$c]) ?>">
                                                <?php if (!empty($g['columnLinks'][$c] ?? '')): ?>
                                                    <a href="index.php?<?= $g['columnLinks'][$c] ?>"><span><?= NumberUtil::normalize($g['collapsedValues'][$c]) ?></span></a>
                                                <?php else: ?>
                                                    <span><?= NumberUtil::normalize($g['collapsedValues'][$c]) ?></span>
                                                <?php endif ?>
                                            </td>
                                        <?php endforeach ?>
                                        <td id="group-avg-<?= $g['id'] ?>" class="totals"
                                            data-direct="<?= NumberUtil::normalize($g['directAverage']) ?>"
                                            data-collapsed="<?= NumberUtil::normalize($g['collapsedAverage']) ?>">
                                            <?= NumberUtil::normalize($g['collapsedAverage']) ?>
                                        </td>
                                        <td id="group-total-<?= $g['id'] ?>" class="totals"
                                            data-direct="<?= NumberUtil::normalize($g['directTotal']) ?>"
                                            data-collapsed="<?= NumberUtil::normalize($g['collapsedTotal']) ?>">
                                            <?= NumberUtil::normalize($g['collapsedTotal']) ?>
                                        </td>
                                    </tr>

                                    <!-- Child rows -->
                                    <?php foreach ($g['rows'] as $r): ?>
                                        <tr class="group-child-<?= $g['id'] ?>" style="display:none">
                                            <td colspan="2">
                                                <?php if (!empty($r['link'])): ?>
                                                    <a href="index.php?<?= $r['link'] ?>"><span><?= str_repeat('&nbsp;', 4) . str_repeat('&#8594; ', 1) . htmlspecialchars($r['label']) ?></span></a>
                                                <?php else: ?>
                                                    <span><?= str_repeat('&nbsp;', 4) . str_repeat('&#8594; ', 1) . htmlspecialchars($r['label']) ?></span>
                                                <?php endif ?>
                                            </td>
                                            <?php foreach ($reportData['columns'] as $c): ?>
                                                <td class="saldos">
                                                    <?php if (!empty($r['columnLinks'][$c] ?? '')): ?>
                                                        <a href="index.php?<?= $r['columnLinks'][$c] ?>"><span><?= NumberUtil::normalize($r['values'][$c]) ?></span></a>
                                                    <?php else: ?>
                                                        <span><?= NumberUtil::normalize($r['values'][$c]) ?></span>
                                                    <?php endif ?>
                                                </td>
                                            <?php endforeach ?>
                                            <td class="totals"><?= NumberUtil::normalize($r['average']) ?></td>
                                            <td class="totals"><?= NumberUtil::normalize($r['total']) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endforeach ?>
                            </tbody>

                            <tfoot>
                                <?php foreach ($reportData['footer'] as $key => $row): ?>
                                    <tr class="<?= $key ?>">
                                        <td colspan="2" class="<?= $key ?>" style="text-align: left;"><?= $label[$key] ?></td>
                                        <?php foreach ($row['values'] as $cell): ?>
                                            <td class="saldos <?= $key ?>"><?= NumberUtil::normalize($cell) ?></td>
                                        <?php endforeach ?>
                                        <td class="totals"><?= NumberUtil::normalize($row['average']) ?></td>
                                        <td class="totals"><?= NumberUtil::normalize($row['total']) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php $ui->footer($label, $footer); ?>
            </div>
        </body>

        </html>
<?php
    }
}
