<?php

namespace PHPLedger\Reports\Builders;

use DateTimeImmutable;

class CategorySummaryReportBuilder
{
    public function build(array $rows, array $columns, string $period, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        if ($period === 'month') {
            $columns = range(1, 12);
        }

        $groups = [];
        $footer = [
            'income'  => ['values' => array_fill_keys($columns, 0.0)],
            'expense' => ['values' => array_fill_keys($columns, 0.0)],
            'totals' => ['values' => array_fill_keys($columns, 0.0)],
            'savings' => ['values' => array_fill_keys($columns, 0.0)],
        ];

        foreach ($rows as $r) {
            $catId    = (int)$r['categoryId'];
            $parentId = (int)$r['parentId'];
            $groupId  = $parentId === 0 ? $catId : $parentId;
            $column   = (int)$r['groupColumn'];
            $value    = (float)$r['amountSum'];
            $savings  = (int)$r['savings'];

            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'id' => $groupId,
                    'label' => $parentId === 0 ? $r['categoryDescription'] : $r['parentDescription'],
                    'direct' => array_fill_keys($columns, 0.0),
                    'childrenTotal' => array_fill_keys($columns, 0.0),
                    'rows' => [],
                    'directTotal' => 0.0,
                    'directAverage' => 0.0,
                    'collapsedValues' => [],
                    'collapsedTotal' => 0.0,
                    'collapsedAverage' => 0.0,
                    'link' => http_build_query([
                        'action' => 'ledger_entries',
                        'filter_entryType' => $groupId,
                        'filter_startDate' => $from->format('Y-m-d'),
                        'filter_endDate' => $to->format('Y-m-d'),
                    ]),
                    'columnLinks' => [],
                ];
            }

            if ($parentId === 0) {
                $groups[$groupId]['direct'][$column] += $value;
                $groups[$groupId]['directTotal'] += $value;
            } else {
                if (!isset($groups[$groupId]['rows'][$catId])) {
                    $groups[$groupId]['rows'][$catId] = [
                        'label' => $r['categoryDescription'],
                        'values' => array_fill_keys($columns, 0.0),
                        'total' => 0.0,
                        'average' => 0.0,
                        'link' => $this->makeLink($groupId, $from, $to),
                        'columnLinks' => [],
                    ];
                }
                $groups[$groupId]['rows'][$catId]['values'][$column] += $value;
                $groups[$groupId]['rows'][$catId]['total'] += $value;
                $groups[$groupId]['childrenTotal'][$column] += $value;

                if (!isset($groups[$groupId]['rows'][$catId]['columnLinks'][$column])) {
                    $groups[$groupId]['rows'][$catId]['columnLinks'][$column] = $this->makeColumnLink($catId, $column, $period, $from);
                }
            }

            if (!isset($groups[$groupId]['columnLinks'][$column])) {
                $groups[$groupId]['columnLinks'][$column] = $this->makeColumnLink($groupId, $column, $period, $from);
            }

            if ($value > 0) {
                $footer['income']['values'][$column] += $value;
            } else {
                $footer['expense']['values'][$column] += $value;
            }
            $footer['totals']['values'][$column] += $value;
            if ($savings === 1) {
                $footer['savings']['values'][$column] += $value;
            }
        }
        foreach ($groups as &$g) {
            foreach ($g['rows'] as &$row) {
                $row['average'] = count($columns) ? $row['total'] / count($columns) : 0.0;
            }
            $g['rows'] = array_values($g['rows']);

            foreach ($columns as $c) {
                $g['collapsedValues'][$c] = $g['direct'][$c] + $g['childrenTotal'][$c];
                $g['collapsedTotal'] += $g['collapsedValues'][$c];
            }

            $g['directAverage'] = count($columns) ? $g['directTotal'] / count($columns) : 0.0;
            $g['collapsedAverage'] = count($columns) ? $g['collapsedTotal'] / count($columns) : 0.0;
        }
        foreach (array_keys($footer) as $k) {
            $footer[$k]['total'] = array_sum($footer[$k]['values']);
            $footer[$k]['average'] = count($columns) ? $footer[$k]['total'] / count($columns) : 0.0;
        }
        return compact('columns', 'groups', 'footer');
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

    private function makeColumnLink(int $entryType, int $column, string $period, DateTimeImmutable $from): string
    {
        if ($period === 'month') {
            $colStart = new DateTimeImmutable(sprintf('%04d-%02d-01', (int)$from->format('Y'), $column));
            $colEnd = $colStart->modify('last day of this month');
        } else {
            $colStart = new DateTimeImmutable($column . '-01-01');
            $colEnd = new DateTimeImmutable($column . '-12-31');
        }
        return $this->makeLink($entryType, $colStart, $colEnd);
    }
}
