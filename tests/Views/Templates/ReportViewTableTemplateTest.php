<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\{
    ReportViewTableTemplate,
    ReportViewTableTopLevelTemplate,
    ReportViewTableChildRowTemplate
};
use PHPLedger\Util\NumberUtil;

beforeEach(function () {
    $this->topLevelTemplate = new ReportViewTableTopLevelTemplate();
    $this->childRowTemplate = new ReportViewTableChildRowTemplate();
    $this->tableTemplate = new ReportViewTableTemplate();
});

it('renders a top-level row correctly', function () {
    $g = [
        'id' => 1,
        'label' => 'Group 1',
        'link' => 'action=group1',
        'rows' => [],
        'direct' => ['col1' => 10],
        'collapsedValues' => ['col1' => 5],
        'directAverage' => 10,
        'collapsedAverage' => 5,
        'directTotal' => 10,
        'collapsedTotal' => 5,
        'columnLinks' => [],
    ];
    $reportData = ['columns' => ['col1']];
    ob_start();
    $this->topLevelTemplate->render(compact('g', 'reportData'));
    $html = ob_get_clean();

    expect($html)->toContain('id="group-head-1"');
    expect($html)->toContain('Group 1');
    expect($html)->toContain('data-direct="10.00"');
    expect($html)->toContain('data-collapsed="5.00"');
});

it('renders child rows correctly', function () {
    $g = [
        'id' => 1,
        'rows' => [
            [
                'label' => 'Child 1',
                'values' => ['col1' => 100],
                'average' => 100,
                'total' => 100,
                'columnLinks' => [],
            ]
        ],
    ];
    $reportData = ['columns' => ['col1']];
    ob_start();
    $this->childRowTemplate->render(compact('g', 'reportData'));
    $html = ob_get_clean();

    expect($html)->toContain('group-child-1');
    expect($html)->toContain('Child 1');
    expect($html)->toContain(NumberUtil::normalize(100));
});

it('renders full report table with footer', function () {
    $reportData = [
        'groups' => [
            [
                'id' => 1,
                'label' => 'G1',
                'link' => 'action=g1',
                'rows' => [],
                'direct' => ['c1' => 10],
                'collapsedValues' => ['c1' => 5],
                'directAverage' => 10,
                'collapsedAverage' => 5,
                'directTotal' => 10,
                'collapsedTotal' => 5,
                'columnLinks' => []
            ]
        ],
        'columns' => ['c1'],
        'footer' => [
            'total' => [
                'values' => ['c1' => 100],
                'average' => 50,
                'total' => 100
            ]
        ]
    ];
    $columnLabels = ['c1'];
    $label = ['category' => 'Category', 'average' => 'Avg', 'total' => 'Total'];

    ob_start();
    $reportViewTableTopLevelTemplate = $this->topLevelTemplate;
    $reportViewTableChildRowTemplate = $this->childRowTemplate;
    $this->tableTemplate->render(compact(
        'reportData',
        'columnLabels',
        'label',
        'reportViewTableTopLevelTemplate',
        'reportViewTableChildRowTemplate'
    ));
    $html = ob_get_clean();

    expect($html)->toContain('<table');
    expect($html)->toContain('Category');
    expect($html)->toContain('Avg');
    expect($html)->toContain('Total');
    expect($html)->toContain('G1');
    expect($html)->toContain(NumberUtil::normalize(100));
});
