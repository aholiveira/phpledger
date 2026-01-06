<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Reports\Builders;

use DateTimeImmutable;

final class CategorySummaryReportBuilder
{
    private array $groups = [];
    private array $footer = [];
    private array $columns = [];
    private string $period;
    private DateTimeImmutable $from;
    private DateTimeImmutable $to;

    public function build(array $rows, array $columns, string $period, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $this->period  = $period;
        $this->from    = $from;
        $this->to      = $to;
        $this->columns = $this->normalizeColumns($columns, $period);
        $this->groups  = [];
        $this->footer  = $this->initFooter($this->columns);

        foreach ($rows['category'] as $row) {
            $this->accumulateRow($row);
        }

        foreach ($rows['savings'] as $col) {
            $this->footer['savings']['values'][$col['groupColumn']] = $col['amountSum'];
        }

        $this->finalizeGroups();
        $this->finalizeFooter();

        return [
            'columns' => $this->columns,
            'groups'  => array_values($this->groups),
            'footer'  => $this->footer,
        ];
    }

    private function normalizeColumns(array $columns, string $period): array
    {
        return $period === 'month' ? range(1, 12) : $columns;
    }

    private function initFooter(array $columns): array
    {
        return [
            'income'  => ['values' => array_fill_keys($columns, 0.0)],
            'expense' => ['values' => array_fill_keys($columns, 0.0)],
            'totals'  => ['values' => array_fill_keys($columns, 0.0)],
            'savings' => ['values' => array_fill_keys($columns, 0.0)],
        ];
    }

    private function accumulateRow(array $r): void
    {
        $catId    = (int)$r['categoryId'];
        $parentId = (int)$r['parentId'];
        $groupId  = $parentId === 0 ? $catId : $parentId;
        $column   = (int)$r['groupColumn'];
        $value    = (float)$r['amountSum'];

        $label = $parentId === 0 ? $r['categoryDescription'] : $r['parentDescription'];
        $this->initGroup($groupId, $label);

        if ($parentId === 0) {
            $this->groups[$groupId]['direct'][$column] += $value;
            $this->groups[$groupId]['directTotal'] += $value;
        } else {
            $this->initChild($groupId, $catId, $r['categoryDescription']);
            $child = &$this->groups[$groupId]['rows'][$catId];
            $child['values'][$column] += $value;
            $child['total'] += $value;
            $this->groups[$groupId]['childrenTotal'][$column] += $value;
            $child['columnLinks'][$column] ??= $this->makeColumnLink($catId, $column);
        }

        $this->groups[$groupId]['columnLinks'][$column] ??= $this->makeColumnLink($groupId, $column);

        $this->accumulateFooter($column, $value);
    }

    private function initGroup(int $groupId, string $label): void
    {
        if (isset($this->groups[$groupId])) {
            return;
        }

        $this->groups[$groupId] = [
            'id' => $groupId,
            'label' => $label,
            'direct' => array_fill_keys($this->columns, 0.0),
            'childrenTotal' => array_fill_keys($this->columns, 0.0),
            'rows' => [],
            'directTotal' => 0.0,
            'directAverage' => 0.0,
            'collapsedValues' => [],
            'collapsedTotal' => 0.0,
            'collapsedAverage' => 0.0,
            'link' => $this->makeLink($groupId, $this->from, $this->to),
            'columnLinks' => [],
        ];
    }

    private function initChild(int $groupId, int $catId, string $label): void
    {
        if (isset($this->groups[$groupId]['rows'][$catId])) {
            return;
        }

        $this->groups[$groupId]['rows'][$catId] = [
            'label' => $label,
            'values' => array_fill_keys($this->columns, 0.0),
            'total' => 0.0,
            'average' => 0.0,
            'link' => $this->makeLink($catId, $this->from, $this->to),
            'columnLinks' => [],
        ];
    }

    private function accumulateFooter(int $column, float $value): void
    {
        if ($value > 0) {
            $this->footer['income']['values'][$column] += $value;
        } else {
            $this->footer['expense']['values'][$column] += $value;
        }
        $this->footer['totals']['values'][$column] += $value;
    }

    private function finalizeGroups(): void
    {
        foreach ($this->groups as $gid => &$g) {
            $this->finalizeGroupRows($g);

            if ($this->shouldRemoveGroup($g)) {
                unset($this->groups[$gid]);
                continue;
            }

            $this->sortGroupRows($g);
            $this->computeGroupCollapsedValues($g);
            $this->computeGroupAverages($g);
        }

        unset($g);

        $this->groups = array_values($this->groups);
        usort($this->groups, fn($a, $b) => strcasecmp($a['label'], $b['label']));
    }

    private function finalizeGroupRows(array &$g): void
    {
        foreach ($g['rows'] as $cid => &$row) {
            if ($this->isZeroRow($row)) {
                unset($g['rows'][$cid]);
                continue;
            }

            $row['average'] = $this->average($row['total']);
        }

        unset($row);
    }

    private function isZeroRow(array $row): bool
    {
        return array_sum($row['values']) == 0.0;
    }

    private function shouldRemoveGroup(array $g): bool
    {
        return empty($g['rows']) && array_sum($g['direct']) == 0.0;
    }

    private function sortGroupRows(array &$g): void
    {
        $g['rows'] = array_values($g['rows']);
        usort($g['rows'], fn($a, $b) => strcasecmp($a['label'], $b['label']));
    }

    private function computeGroupCollapsedValues(array &$g): void
    {
        foreach ($this->columns as $c) {
            $g['collapsedValues'][$c] = $g['direct'][$c] + $g['childrenTotal'][$c];
            $g['collapsedTotal'] += $g['collapsedValues'][$c];
        }
    }

    private function computeGroupAverages(array &$g): void
    {
        $g['directAverage'] = $this->average($g['directTotal']);
        $g['collapsedAverage'] = $this->average($g['collapsedTotal']);
    }

    private function average(float $total): float
    {
        return round(count($this->columns) ? $total / count($this->columns) : 0.0, 3);
    }

    private function finalizeFooter(): void
    {
        foreach ($this->footer as &$row) {
            $row['total'] = array_sum($row['values']);
            $row['average'] = $this->average($row['total']);
        }
    }

    private function makeLink(int $entryType, DateTimeImmutable $from, DateTimeImmutable $to): string
    {
        return http_build_query([
            'action' => 'ledger_entries',
            'filter_entryType' => $entryType,
            'filter_startDate' => $from->format('Y-m-d'),
            'filter_endDate' => $to->format('Y-m-d'),
        ]);
    }

    private function makeColumnLink(int $entryType, int $column): string
    {
        if ($this->period === 'month') {
            $start = new DateTimeImmutable(sprintf('%04d-%02d-01', (int)$this->from->format('Y'), $column));
            $end   = $start->modify('last day of this month');
        } else {
            $start = new DateTimeImmutable($column . '-01-01');
            $end   = new DateTimeImmutable($column . '-12-31');
        }
        return $this->makeLink($entryType, $start, $end);
    }
}
