<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\NumberUtil;

final class ReportViewTableTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
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
                    <?php $reportViewTableTopLevelTemplate->render(compact('g', 'reportData')); ?>
                    <?php $reportViewTableChildRowTemplate->render(compact('g', 'reportData')); ?>
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
<?php
    }
}
