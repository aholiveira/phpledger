<?php

namespace PHPLedgerTests\Unit\Views\Templates;

use PHPLedger\Contracts\UiBuilderInterface;
use PHPLedger\Views\Templates\EntryCategoryFormViewTemplate;
use PHPLedger\Util\Html;

uses()->group('view-templates');

beforeEach(function () {
    $ui = new class implements UiBuilderInterface {
        public function menu(array $text, array $menu): void {}
        public function footer(array $text, array $footer): void {}
    };
    $this->template = new EntryCategoryFormViewTemplate();

    $this->data = [
        'title' => 'Entry Category',
        'lang' => 'en-us',
        'label' => [
            'id' => 'ID',
            'category' => 'Category',
            'description' => 'Description',
            'active' => 'Active',
            'save' => 'Save',
            'delete' => 'Delete',
            'are_you_sure_you_want_to_delete' => 'Are you sure?',
        ],
        'menu' => [],
        'footer' => [],
        'ui' => $ui,
        'csrf' => '<input type="hidden" name="_csrf_token" value="token123">',
        'text' => [
            'id' => '1',
            'description' => 'Test description',
            'active' => 'checked',
        ],
        'parentRows' => [
            'rows' => [
                ['id' => 0, 'text' => 'None', 'value' => 0, 'parentId' => 0, 'selected' => false],
                ['id' => 1, 'text' => 'Category 1', 'value' => 0, 'parentId' => 0, 'value' => 1, 'selected' => false],
            ]
        ],
    ];
});

function captureRender(callable $callback): string
{
    ob_start();
    $callback();
    return ob_get_clean();
}

it('renders HTML output without error', function () {
    $output = captureRender(fn() => $this->template->render($this->data));

    expect($output)->toContain('<form method="POST" action="index.php?action=entry_types"');
    expect($output)->toContain('name="id" value="1"');
    expect($output)->toContain('name="description" value="Test description"');
    expect($output)->toContain('name="active" checked');
    expect($output)->toContain('<input type="hidden" name="_csrf_token" value="token123">');
});

it('includes all select options for parent categories', function () {
    $output = captureRender(fn() => $this->template->render($this->data));

    foreach ($this->data['parentRows']['rows'] as $row) {
        expect($output)->toContain('<option value="' . $row['value'] . '" >' . $row['text'] . '</option>');
    }
});

it('renders buttons with correct labels and confirmation', function () {
    $output = captureRender(fn() => $this->template->render($this->data));

    expect($output)->toContain('<button type="submit" name="update" value="save">Save</button>');
    expect($output)->toContain(
        '<button type="submit" name="update" value="delete" onclick="return confirm(\'Are you sure?\');">Delete</button>'
    );
});

it('renders title correctly', function () {
    $output = captureRender(fn() => $this->template->render($this->data));
    expect($output)->toContain('<title>' . Html::title('Entry Category') . '</title>');
});
