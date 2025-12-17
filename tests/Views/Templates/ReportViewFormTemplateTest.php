<?php

use PHPLedger\Views\Templates\ReportViewFormTemplate;

beforeEach(function () {
    $this->template = new ReportViewFormTemplate();
    $this->data = [
        'lang' => 'en',
        'filterFields' => [
            ['id' => 'f1', 'label' => 'Field 1', 'type' => 'text', 'value' => 'val1', 'selected' => false],
            ['id' => 'f2', 'label' => 'Field 2', 'type' => 'number', 'value' => '123', 'selected' => false],
        ],
        'label' => [
            'period' => 'Period',
            'calculate' => 'Calculate',
        ],
        'periodOptions' => [
            ['value' => 'daily', 'text' => 'Daily', 'selected' => false],
            ['value' => 'monthly', 'text' => 'Monthly', 'selected' => false],
        ],
    ];
});

it('renders the form with hidden fields', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<form name="filtro" method="GET">');
    expect($output)->toContain('<input type="hidden" name="action" value="report">');
    expect($output)->toContain('<input type="hidden" name="lang" value="en">');
});

it('renders all filter fields', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    foreach ($this->data['filterFields'] as $f) {
        expect($output)->toContain('<label for="' . $f['id'] . '">' . $f['label'] . '</label>');
        expect($output)->toContain('name="' . $f['id'] . '"');
        expect($output)->toContain('value="' . $f['value'] . '"');
        expect($output)->toContain('type="' . $f['type'] . '"');
    }
});

it('renders the period select with options', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<label for="period">' . $this->data['label']['period'] . '</label>');
    foreach ($this->data['periodOptions'] as $opt) {
        expect($output)->toContain('<option value="' . $opt['value'] . '" >' . $opt['text'] . '</option>');
    }
});

it('renders the calculate button', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<button type="submit" value="subaction" value="calculate">' . $this->data['label']['calculate'] . '</button>');
});
