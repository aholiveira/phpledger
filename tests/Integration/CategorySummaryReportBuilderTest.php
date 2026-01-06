<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Reports\Builders\CategorySummaryReportBuilder;

beforeEach(function () {
    $this->builder = new CategorySummaryReportBuilder();
    $this->from = new DateTimeImmutable('2024-01-01');
    $this->to   = new DateTimeImmutable('2024-12-31');
});

it('builds empty report when no rows', function () {
    $result = $this->builder->build(['category' => [], 'savings' => []], [2024], 'year', $this->from, $this->to);

    expect($result['groups'])->toBeEmpty()
        ->and($result['footer']['totals']['total'])->toBe(0.0);
});

it('accumulates direct category values', function () {
    $rows['savings'] = [];
    $rows['category'] = [[
        'categoryId' => 1,
        'parentId' => 0,
        'groupColumn' => 2024,
        'amountSum' => 100,
        'categoryDescription' => 'Income',
        'parentDescription' => '',
    ]];

    $r = $this->builder->build($rows, [2024], 'year', $this->from, $this->to);

    expect($r['groups'])->toHaveCount(1)
        ->and($r['groups'][0]['direct'][2024])->toBe(100.0)
        ->and($r['footer']['income']['values'][2024])->toBe(100.0);
});

it('accumulates child category values', function () {
    $rows['savings'] = [];
    $rows['category'] = [[
        'categoryId' => 2,
        'parentId' => 1,
        'groupColumn' => 2024,
        'amountSum' => -50,
        'categoryDescription' => 'Food',
        'parentDescription' => 'Expenses',
    ]];

    $r = $this->builder->build($rows, [2024], 'year', $this->from, $this->to);

    expect($r['groups'][0]['rows'])->toHaveCount(1)
        ->and($r['groups'][0]['rows'][0]['total'])->toBe(-50.0)
        ->and($r['footer']['expense']['values'][2024])->toBe(-50.0);
});

it('calculates collapsed totals', function () {
    $rows['savings'] = [];
    $rows['category'] = [
        [
            'categoryId' => 1,
            'parentId' => 0,
            'groupColumn' => 2024,
            'amountSum' => 100,
            'categoryDescription' => 'Income',
            'parentDescription' => '',
        ],
        [
            'categoryId' => 2,
            'parentId' => 1,
            'groupColumn' => 2024,
            'amountSum' => -30,
            'categoryDescription' => 'Tax',
            'parentDescription' => 'Income',
        ],
    ];

    $r = $this->builder->build($rows, [2024], 'year', $this->from, $this->to);

    expect($r['groups'][0]['collapsedTotal'])->toBe(70.0);
});

it('normalizes month columns automatically', function () {
    $rows = ['category' => [[
        'categoryId' => 1,
        'parentId' => 0,
        'groupColumn' => 1,
        'amountSum' => 10,
        'categoryDescription' => 'Income',
        'parentDescription' => '',
    ]], 'savings' => []];

    $r = $this->builder->build($rows, [], 'month', $this->from, $this->to);

    expect($r['columns'])->toBe(range(1, 12))
        ->and($r['groups'][0]['direct'][1])->toBe(10.0);
});

it('updates savings columns', function () {
    $rows = ['category' => [], 'savings' => [['groupColumn' => 11, 'amountSum' => 200.0]]];

    $r = $this->builder->build($rows, [], 'month', $this->from, $this->to);

    expect($r['columns'])->toBe(range(1, 12))
        ->and($r['footer']['savings']['values'][11])->toBe(200.0);
});
