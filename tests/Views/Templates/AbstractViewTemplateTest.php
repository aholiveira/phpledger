<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\AbstractViewTemplate;

beforeEach(function () {
    $this->view = new class extends AbstractViewTemplate {
        public function render(array $data): void {}
        public function callRenderSelectOptions(array $optionList): string
        {
            ob_start();
            $this->renderSelectOptions($optionList);
            return ob_get_clean();
        }
        public function callRenderOptionRow(array $row): string
        {
            ob_start();
            $this->renderOptionRow($row);
            return ob_get_clean();
        }
    };
});

it('renders a single option row correctly', function () {
    $row = ['value' => 1, 'text' => 'Option 1', 'selected' => true];
    $html = $this->view->callRenderOptionRow($row);

    expect($html)->toContain('<option value="1" selected>Option 1</option>');
});

it('renders multiple options without children correctly', function () {
    $optionList = [
        ['value' => 1, 'text' => 'Parent 1', 'parentId' => 0, 'selected' => false],
        ['value' => 2, 'text' => 'Parent 2', 'parentId' => 0, 'selected' => false],
    ];

    $html = $this->view->callRenderSelectOptions($optionList);

    expect($html)->toContain('<option value="1" >Parent 1</option>');
    expect($html)->toContain('<option value="2" >Parent 2</option>');
});

it('renders option with children using optgroup', function () {
    $optionList = [
        ['value' => 1, 'text' => 'Parent', 'parentId' => 0, 'selected' => false],
        ['value' => 2, 'text' => 'Child 1', 'parentId' => 1, 'selected' => false],
        ['value' => 3, 'text' => 'Child 2', 'parentId' => 1, 'selected' => true],
    ];

    $html = $this->view->callRenderSelectOptions($optionList);

    expect($html)->toContain('<optgroup label="Parent">');
    expect($html)->toContain('<option value="1" >Parent</option>');
    expect($html)->toContain('<option value="2" >Child 1</option>');
    expect($html)->toContain('<option value="3" selected>Child 2</option>');
});

it('returns empty string if no top-level options', function () {
    $optionList = [
        ['value' => 2, 'text' => 'Child', 'parentId' => 1, 'selected' => false],
    ];

    $html = $this->view->callRenderSelectOptions($optionList);

    expect($html)->toBe('');
});
