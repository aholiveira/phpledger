<?php

namespace PHPLedgerTests\Unit\Views;

use PHPLedger\Views\Templates\AbstractViewTemplate;

class DummyTemplate extends AbstractViewTemplate
{
    public function render(array $data): void {}

    public function testRenderSelectOptions(array $optionList): string
    {
        ob_start();
        $this->renderSelectOptions($optionList);
        return ob_get_clean();
    }

    public function testRenderOptionRow(array $row): string
    {
        ob_start();
        $this->renderOptionRow($row);
        return ob_get_clean();
    }
}

beforeEach(function () {
    $this->tpl = new DummyTemplate();
});

it('renders a flat option row correctly', function () {
    $row = ['value' => '123', 'text' => 'Hello', 'selected' => false, 'parentId' => 0];
    $html = $this->tpl->testRenderOptionRow($row);
    expect($html)->toContain('<option value="123" >Hello</option>');
});

it('renders a selected option row correctly', function () {
    $row = ['value' => 'abc', 'text' => 'XYZ', 'selected' => true, 'parentId' => 0];
    $html = $this->tpl->testRenderOptionRow($row);
    expect($html)->toContain('<option value="abc" selected>XYZ</option>');
});

it('renders flat rows without children', function () {
    $options = [
        ['value' => '1', 'text' => 'One', 'selected' => false, 'parentId' => 0],
        ['value' => '2', 'text' => 'Two', 'selected' => true, 'parentId' => 0],
    ];
    $html = $this->tpl->testRenderSelectOptions($options);
    expect($html)->toContain('<option value="1" >One</option>');
    expect($html)->toContain('<option value="2" selected>Two</option>');
    expect($html)->not->toContain('<optgroup');
});

it('renders parent with child as optgroup', function () {
    $options = [
        ['value' => 1, 'text' => 'Group A', 'selected' => false, 'parentId' => 0],
        ['value' => 2, 'text' => 'A1', 'selected' => false, 'parentId' => 1],
        ['value' => 3, 'text' => 'A2', 'selected' => true, 'parentId' => 1],
    ];
    $html = $this->tpl->testRenderSelectOptions($options);
    expect($html)->toContain('<optgroup label="Group A">');
    expect($html)->toContain('<option value="1" >Group A</option>');
    expect($html)->toContain('<option value="2" >A1</option>');
    expect($html)->toContain('<option value="3" selected>A2</option>');
    expect($html)->toContain('</optgroup>');
});

it('renders mixed parent and flat options', function () {
    $options = [
        ['value' => 10, 'text' => 'Group X', 'selected' => false, 'parentId' => 0],
        ['value' => 11, 'text' => 'X1', 'selected' => false, 'parentId' => 10],
        ['value' => 20, 'text' => 'Standalone', 'selected' => false, 'parentId' => 0],
    ];
    $html = $this->tpl->testRenderSelectOptions($options);
    expect($html)->toContain('<optgroup label="Group X">');
    expect($html)->toContain('<option value="10" >Group X</option>');
    expect($html)->toContain('<option value="11" >X1</option>');
    expect($html)->toContain('<option value="20" >Standalone</option>');
});
