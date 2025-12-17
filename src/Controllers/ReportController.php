<?php

namespace PHPLedger\Controllers;

use DateTimeImmutable;
use PHPLedger\Reports\Builders\CategorySummaryReportBuilder;
use PHPLedger\Util\CsvBuilder;
use PHPLedger\Views\Templates\ReportViewFormTemplate;
use PHPLedger\Views\Templates\ReportViewTableChildRowTemplate;
use PHPLedger\Views\Templates\ReportViewTableTemplate;
use PHPLedger\Views\Templates\ReportViewTableTopLevelTemplate;
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
        $export = strtolower($this->request->input('export', ''));
        if ($export === 'csv') {
            $this->reportDownload($reportData);
            return;
        }
        if ($export === 'csv_raw') {
            $this->rawDataDownload($raw['category']);
            return;
        }
        $this->uiData['label'] = array_merge($this->uiData['label'], $this->buildL10nLabels(
            $l10n,
            [
                'income',
                'expense',
                'savings',
                'category',
                'average',
                'totals',
                'total',
                'calculate',
                'period',
                'download_report_csv',
                'download_raw_csv',
                'download_data',
                'download_raw_data',
            ]
        ));
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
            'downloadUrl' => "index.php?" . http_build_query(array_merge($this->request->all(), ['export' => 'csv'])),
            'downloadRawUrl' => "index.php?" . http_build_query(array_merge($this->request->all(), ['export' => 'csv_raw'])),
            'reportViewFormTemplate' => new ReportViewFormTemplate(),
            'reportViewTableTemplate' => new ReportViewTableTemplate(),
            'reportViewTableTopLevelTemplate' => new ReportViewTableTopLevelTemplate(),
            'reportViewTableChildRowTemplate' => new ReportViewTableChildRowTemplate(),
        ]));
    }

    private function reportDownload(array $data): void
    {
        $headers = array_merge(['Category'], $data['columns'], ['Average', 'Total']);
        $rows = [];
        foreach ($data['groups'] as $g) {
            $rows[] = array_merge([$g['label']], array_values($g['collapsedValues']), [$g['collapsedAverage'], $g['collapsedTotal']]);
            foreach ($g['rows'] as $r) {
                $rows[] = array_merge(['→ ' . $r['label']], array_values($r['values']), [$r['average'], $r['total']]);
            }
        }
        $this->app->fileResponseSender()->csv(CsvBuilder::build($headers, $rows, ','), 'report.csv');
    }

    private function rawDataDownload(array $rawData): void
    {
        $this->app->fileResponseSender()->csv(CsvBuilder::build(array_keys($rawData[0]), $rawData), "raw_report.csv");
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
        if ($endYear < $startYear) {
            [$startYear, $endYear] = [$endYear, $startYear];
        }
        if (($endYear - $startYear + 1) > self::MAX_YEAR_RANGE) {
            $endYear = $startYear + self::MAX_YEAR_RANGE - 1;
        }

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
