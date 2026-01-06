<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\NumberUtil;

final class ReportViewTableChildRowTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
?>
        <!-- Child rows -->
        <?php foreach ($g['rows'] as $r): ?>
            <tr class="group-child-<?= $g['id'] ?>" style="display:none">
                <td colspan="2">
                    <?php if (!empty($r['link'])): ?>
                        <a href="index.php?<?= $r['link'] ?>"><span><?= '&#8594; ' . htmlspecialchars($r['label']) ?></span></a>
                    <?php else: ?>
                        <span><?= '&#8594; ' . htmlspecialchars($r['label']) ?></span>
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
<?php
    }
}
