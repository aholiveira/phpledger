<?php

use PHPLedger\Views\Templates\ReportViewTableChildRowTemplate;
use PHPLedger\Util\NumberUtil;

beforeEach(function () {
    $this->template = new ReportViewTableChildRowTemplate();

    $this->data = [
        'g' => [
            'id' => 1,
            'rows' => [
                [
                    'label' => 'Child 1',
                    'link' => 'child=1',
                    'values' => ['col1' => 10.5, 'col2' => 20.25],
                    'columnLinks' => ['col1' => 'link1'],
                    'average' => 15.375,
                    'total' => 30.75,
                ],
                [
                    'label' => 'Child 2',
                    'values' => ['col1' => 5, 'col2' => 15],
                    'average' => 10,
                    'total' => 20,
                ],
            ],
        ],
        'reportData' => [
            'columns' => ['col1', 'col2'],
        ],
    ];
});

it('renders child rows with correct class and style', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    foreach ($this->data['g']['rows'] as $r) {
        expect($output)->toContain('class="group-child-1" style="display:none"');
    }
});

it('renders row labels with links when provided', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<a href="index.php?child=1"><span>&#8594; Child 1</span></a>');
});

it('renders row labels without links when not provided', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<span>&#8594; Child 2</span>');
});

it('renders column values with links when provided', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    $val = NumberUtil::normalize($this->data['g']['rows'][0]['values']['col1']);
    expect($output)->toContain('<a href="index.php?link1"><span>' . $val . '</span></a>');
});

it('renders column values without links when not provided', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    $val = NumberUtil::normalize($this->data['g']['rows'][1]['values']['col1']);
    expect($output)->toContain('<span>' . $val . '</span>');
});

it('renders average and total correctly', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    foreach ($this->data['g']['rows'] as $r) {
        $avg = NumberUtil::normalize($r['average']);
        $total = NumberUtil::normalize($r['total']);
        expect($output)->toContain('<td class="totals">' . $avg . '</td>');
        expect($output)->toContain('<td class="totals">' . $total . '</td>');
    }
});
