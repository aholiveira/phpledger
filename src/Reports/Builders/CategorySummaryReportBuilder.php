<?php

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

        foreach ($rows as $row) {
            $this->accumulateRow($row);
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
        $savings  = (int)$r['savings'];

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

        $this->accumulateFooter($column, $value, $savings);
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
            'link' => $this->makeLink($groupId, $this->from, $this->to),
            'columnLinks' => [],
        ];
    }

    private function accumulateFooter(int $column, float $value, int $savings): void
    {
        if ($value > 0) {
            $this->footer['income']['values'][$column] += $value;
        } else {
            $this->footer['expense']['values'][$column] += $value;
        }
        $this->footer['totals']['values'][$column] += $value;
        if ($savings === 1) {
            $this->footer['savings']['values'][$column] += $value;
        }
    }

    private function finalizeGroups(): void
    {
        foreach ($this->groups as $gid => &$g) {
            foreach ($g['rows'] as $cid => &$row) {
                if (array_sum($row['values']) == 0.0) {
                    unset($g['rows'][$cid]);
                    continue;
                }
                $row['average'] = count($this->columns) ? $row['total'] / count($this->columns) : 0.0;
            }

            if (empty($g['rows']) && array_sum($g['direct']) == 0.0) {
                unset($this->groups[$gid]);
                continue;
            }

            $g['rows'] = array_values($g['rows']);

            foreach ($this->columns as $c) {
                $g['collapsedValues'][$c] = $g['direct'][$c] + $g['childrenTotal'][$c];
                $g['collapsedTotal'] += $g['collapsedValues'][$c];
            }

            $g['directAverage'] = count($this->columns) ? $g['directTotal'] / count($this->columns) : 0.0;
            $g['collapsedAverage'] = count($this->columns) ? $g['collapsedTotal'] / count($this->columns) : 0.0;
        }
    }

    private function finalizeFooter(): void
    {
        foreach ($this->footer as $k => &$row) {
            $row['total'] = array_sum($row['values']);
            $row['average'] = count($this->columns) ? $row['total'] / count($this->columns) : 0.0;
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
