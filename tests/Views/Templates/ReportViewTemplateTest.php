<?php

use PHPLedger\Contracts\UiBuilderInterface;
use PHPLedger\Views\Templates\ReportViewTemplate;

beforeEach(function () {
    $this->template = new ReportViewTemplate();
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
    };

    $this->formTemplateMock = Mockery::mock();
    $this->formTemplateMock->shouldReceive('render')->andReturnNull();

    $this->tableTemplateMock = Mockery::mock();
    $this->tableTemplateMock->shouldReceive('render')->andReturnNull();

    $this->topLevelTemplateMock = Mockery::mock();
    $this->topLevelTemplateMock->shouldReceive('render')->andReturnNull();

    $this->childRowTemplateMock = Mockery::mock();
    $this->childRowTemplateMock->shouldReceive('render')->andReturnNull();

    $this->defaultData = [
        'lang' => 'en-us',
        'pagetitle' => 'Report',
        'appTitle' => 'AppTitle',
        'ui' => $ui,
        'menu' => [],
        'label' => ['download_data' => 'Download', 'download_raw_data' => 'Download raw'],
        'footer' => [],
        'periodOptions' => [],
        'filterFields' => [],
        'period' => '2025-01',
        'reportData' => ['groups' => [], 'columns' => [], 'footer' => []],
        'columnLabels' => [],
        'reportViewFormTemplate' => $this->formTemplateMock,
        'reportViewTableTemplate' => $this->tableTemplateMock,
        'reportViewTableTopLevelTemplate' => $this->topLevelTemplateMock,
        'reportViewTableChildRowTemplate' => $this->childRowTemplateMock,
        'downloadUrl' => '/download.csv',
        'downloadRawUrl' => '/download_raw.csv',
    ];
});

it('renders the report view template without errors', function () {
    ob_start();
    $this->template->render($this->defaultData);
    $html = ob_get_clean();

    expect($html)->toContain('<!DOCTYPE html>');
    expect($html)->toContain('<html lang="en-us">');
    expect($html)->toMatch('/<title>Report.*<\/title>/');
    expect($html)->toContain('assets/js/common.js');
    expect($html)->toContain(htmlspecialchars($this->defaultData['downloadUrl']));
    expect($html)->toContain(htmlspecialchars($this->defaultData['downloadRawUrl']));
});
