<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\NumberUtil;

final class ReportViewTableTopLevelTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!-- Top-level row -->
        <tr id="group-head-<?= $g['id'] ?>" data-expanded="0">
            <?php if (!empty($g['rows'])): ?>
                <td style="width: min-content; cursor:pointer"> <button type="button"
                        class="toggle-btn"
                        onclick="toggleGroup('<?= $g['id'] ?>')"
                        aria-expanded="false"
                        aria-controls="group-<?= $g['id'] ?>">
                        +
                    </button>
                </td>
            <?php else: ?>
                <td></td>
            <?php endif ?>
            <td><a href="index.php?<?= $g['link'] ?>" aria-label="<?= $this->htmlSafe($g['label']) ?>"><?= $this->htmlSafe($g['label']) ?></a></td>
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
<?php
    }
}
