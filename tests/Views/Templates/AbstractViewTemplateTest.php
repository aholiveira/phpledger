<?php

namespace PHPLedgerTests\Unit\Views;

use PHPLedger\Views\Templates\AbstractViewTemplate;

class DummyTemplate extends AbstractViewTemplate
{
    public array $captured;

    public function render(array $data): void
    {
        // not used — tests call protected method through wrapper
    }

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
    $row = [
        'value' => '123',
        'text' => 'Hello',
        'selected' => false,
    ];

    $html = $this->tpl->testRenderOptionRow($row);

    expect($html)->toContain('<option value="123" >Hello</option>');
});

it('renders a selected option row correctly', function () {
    $row = [
        'value' => 'abc',
        'text' => 'XYZ',
        'selected' => true,
    ];

    $html = $this->tpl->testRenderOptionRow($row);

    expect($html)->toContain('<option value="abc" selected>XYZ</option>');
});

it('renders flat rows (no optgroup)', function () {
    $options = [
        'rows' => [
            ['value' => '1', 'text' => 'One', 'selected' => false],
            ['value' => '2', 'text' => 'Two', 'selected' => true],
        ],
    ];

    $html = $this->tpl->testRenderSelectOptions($options);

    expect($html)->toContain('<option value="1" >One</option>');
    expect($html)->toContain('<option value="2" selected>Two</option>');
    expect($html)->not->toContain('<optgroup');
});

it('renders optgroup with sub-options when text is an array', function () {
    $options = [
        'rows' => [
            [
                'label' => 'Group A',
                'text' => [
                    ['value' => 'ga1', 'text' => 'A1', 'selected' => false],
                    ['value' => 'ga2', 'text' => 'A2', 'selected' => true],
                ],
            ],
        ],
    ];

    $html = $this->tpl->testRenderSelectOptions($options);

    expect($html)->toContain('<optgroup label="Group A">');
    expect($html)->toContain('<option value="ga1" >A1</option>');
    expect($html)->toContain('<option value="ga2" selected>A2</option>');
    expect($html)->toContain('</optgroup>');
});

it('handles mixed groups and flat rows together', function () {
    $options = [
        'rows' => [
            [
                'label' => 'Group X',
                'text' => [
                    ['value' => 'x1', 'text' => 'X1', 'selected' => false],
                ],
            ],
            [
                'value' => 'solo',
                'text' => 'Standalone',
                'selected' => false,
            ],
        ],
    ];

    $html = $this->tpl->testRenderSelectOptions($options);

    expect($html)->toContain('<optgroup label="Group X">');
    expect($html)->toContain('<option value="x1" >X1</option>');
    expect($html)->toContain('<option value="solo" >Standalone</option>');
});
