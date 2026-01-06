<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\ReportViewTableTopLevelTemplate;
use PHPLedger\Util\NumberUtil;

beforeEach(function () {
    $this->template = new ReportViewTableTopLevelTemplate();
});

it('renders a top-level row with toggle button if child rows exist', function () {
    $g = [
        'id' => 1,
        'label' => 'Category 1',
        'rows' => [['id' => 101]],
        'link' => 'action=category1',
        'direct' => ['col1' => 10],
        'collapsedValues' => ['col1' => 5],
        'columnLinks' => ['col1' => 'action=col1'],
        'directAverage' => 10,
        'collapsedAverage' => 5,
        'directTotal' => 10,
        'collapsedTotal' => 5,
    ];

    $reportData = ['columns' => ['col1']];

    ob_start();
    $this->template->render(compact('g', 'reportData'));
    $html = ob_get_clean();

    expect($html)->toContain('id="group-head-1"');
    expect($html)->toContain('toggleGroup(\'1\')');
    expect($html)->toContain('Category 1');
    expect($html)->toContain('index.php?action=col1');
    expect($html)->toContain(NumberUtil::normalize(5));
    expect($html)->toContain('id="group-avg-1"');
    expect($html)->toContain('id="group-total-1"');
});

it('renders a top-level row without toggle button if no child rows', function () {
    $g = [
        'id' => 2,
        'label' => 'Category 2',
        'rows' => [],
        'link' => 'action=category2',
        'direct' => ['col1' => 15],
        'collapsedValues' => ['col1' => 15],
        'columnLinks' => [],
        'directAverage' => 15,
        'collapsedAverage' => 15,
        'directTotal' => 15,
        'collapsedTotal' => 15,
    ];

    $reportData = ['columns' => ['col1']];

    ob_start();
    $this->template->render(compact('g', 'reportData'));
    $html = ob_get_clean();

    expect($html)->toContain('id="group-head-2"');
    expect($html)->not->toContain('toggleGroup(\'2\')');
    expect($html)->toContain('Category 2');
    expect($html)->toContain(NumberUtil::normalize(15));
});

it('renders multiple columns correctly', function () {
    $g = [
        'id' => 3,
        'label' => 'Category 3',
        'rows' => [],
        'link' => 'action=category3',
        'direct' => ['col1' => 1, 'col2' => 2],
        'collapsedValues' => ['col1' => 3, 'col2' => 4],
        'columnLinks' => ['col2' => 'action=col2'],
        'directAverage' => 1.5,
        'collapsedAverage' => 3.5,
        'directTotal' => 3,
        'collapsedTotal' => 7,
    ];

    $reportData = ['columns' => ['col1', 'col2']];

    ob_start();
    $this->template->render(compact('g', 'reportData'));
    $html = ob_get_clean();

    expect($html)->toContain(NumberUtil::normalize(3));
    expect($html)->toContain(NumberUtil::normalize(4));
    expect($html)->toContain('action=col2');
    expect($html)->toContain(NumberUtil::normalize(3.5)); // collapsedAverage
    expect($html)->toContain(NumberUtil::normalize(7));   // collapsedTotal
});
