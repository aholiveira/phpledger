<?php

namespace PHPLedger\Controllers;

use DateTimeImmutable;
use PHPLedger\Reports\Builders\CategorySummaryReportBuilder;
use PHPLedger\Views\Templates\ReportViewTemplate;

final class ReportController extends AbstractViewController
{
    private const MAX_YEAR_RANGE = 10;

    protected function handle(): void
    {
        $period = strtolower((string)$this->request->input('period', 'month'));
        $l10n = $this->app->l10n();
        if ($period !== 'month' && $period !== 'year') {
            $period = 'month';
        }
        [$from, $to, $filters, $columns] = $this->resolvePeriod($period);
        if ($period === 'month') {
            $monthNames = array_map(fn($n) => $l10n->l("mon_$n"), $columns);
        }
        $raw = $this->app->reportFactory()::categorySummary()->fetch($from, $to, $period);
        $builder = new CategorySummaryReportBuilder();
        $reportData = $builder->build($raw, $columns, $period, $from, $to);
        if (strtolower($this->request->input('subaction', '')) === 'download') {
            $this->reportDownload($reportData);
        }
        if (strtolower($this->request->input('subaction', '')) === 'download_raw') {
            $this->rawDataDownload($raw);
        }
        $this->uiData['label'] = array_merge($this->uiData['label'], [
            'income' => $l10n->l('income'),
            'expense' => $l10n->l('expense'),
            'savings' => $l10n->l('savings'),
            'category' => $l10n->l('category'),
            'average' => $l10n->l('average'),
            'totals' => $l10n->l('total'),
            'total' => $l10n->l('total'),
            'calculate' => $l10n->l('calculate'),
            'period' => $l10n->l('period'),
        ]);
        (new ReportViewTemplate())->render(array_merge($this->uiData, [
            'pagetitle'    => $period === 'month' ? $l10n->l('report_month') : $l10n->l('report_year'),
            'columnLabels' => $period === 'month' ? $monthNames : $columns,
            'filterFields' => $filters,
            'reportData'   => $reportData,
            'periodOptions' => [
                ['text' => $l10n->l('month'), 'value' => 'month', 'selected' => $period === 'month'],
                ['text' => $l10n->l('year'), 'value' => 'year', 'selected' => $period === 'year']
            ],
            'period'       => $period,
            'action'       => $this->request->input('action', ''),
        ]));
    }

    public function reportDownload(array $data): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');
        $out = fopen('php://output', 'w');

        // Column headers
        fputcsv($out, array_merge(['Category'], $data['columns'], ['Average', 'Total']), ',', '"', '\\');

        foreach ($data['groups'] as $g) {
            fputcsv($out, array_merge([$g['label']], array_values($g['collapsedValues']), [$g['collapsedAverage'], $g['collapsedTotal']]), ',', '"', '\\');
            foreach ($g['rows'] as $r) {
                fputcsv($out, array_merge(['→ ' . $r['label']], array_values($r['values']), [$r['average'], $r['total']]), ',', '"', '\\');
            }
        }
        fclose($out);
        exit;
    }

    public function rawDataDownload(array $rawData): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="raw_report.csv"');
        $out = fopen('php://output', 'w');

        if (!empty($rawData)) {
            fputcsv($out, array_keys($rawData[0]), ',', '"', '\\'); // header
            foreach ($rawData as $row) {
                fputcsv($out, $row, ',', '"', '\\');
            }
        }

        fclose($out);
        exit;
    }

    private function resolvePeriod(string $period): array
    {
        $currentYear = (int)date('Y');
        $l10n = $this->app->l10n();
        if ($period === 'month') {
            $year = (int)$this->request->input('year', $currentYear);
            $from = new DateTimeImmutable("$year-01-01");
            $to   = new DateTimeImmutable("$year-12-31");

            return [
                $from,
                $to,
                [['id' => 'year', 'type' => 'number', 'label' => $l10n->l('year'), 'value' => $year]],
                range(1, 12)
            ];
        }

        $startYear = (int)$this->request->input('startYear', $currentYear - 1);
        $endYear   = (int)$this->request->input('endYear', $currentYear);
        if ($endYear < $startYear) [$startYear, $endYear] = [$endYear, $startYear];
        if (($endYear - $startYear + 1) > self::MAX_YEAR_RANGE) $endYear = $startYear + self::MAX_YEAR_RANGE - 1;

        return [
            new DateTimeImmutable("$startYear-01-01"),
            new DateTimeImmutable("$endYear-12-31"),
            [
                ['id' => 'startYear', 'type' => 'number', 'label' => $l10n->l('start_year'), 'value' => $startYear],
                ['id' => 'endYear', 'type' => 'number', 'label' => $l10n->l('end_year'), 'value' => $endYear]
            ],
            range($startYear, $endYear)
        ];
    }
}
