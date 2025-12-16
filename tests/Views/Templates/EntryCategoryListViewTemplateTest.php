<?php

use PHPLedger\Views\Templates\EntryCategoryListViewTemplate;
use PHPLedger\Util\Html;

beforeEach(function () {
    $this->template = new EntryCategoryListViewTemplate();

    $this->data = [
        'lang' => 'en',
        'title' => 'Test Page',
        'message' => 'Test message',
        'success' => true,
        'label' => [
            'add' => 'Add',
            'id' => 'ID',
            'description' => 'Description',
            'amount' => 'Amount',
            'active' => 'Active',
            'actions' => 'Actions',
            'edit' => 'Edit',
            'edit_category' => 'Edit Category'
        ],
        'menu' => ['home' => '/home'],
        'footer' => [],
        'rows' => [
            ['id' => 1, 'description' => 'Category 1', 'amount' => 10, 'active' => true, 'parentId' => 0, 'href' => '/edit/1'],
            ['id' => 2, 'description' => 'Category 2', 'amount' => 20, 'active' => false, 'parentId' => 1, 'href' => '/edit/2'],
        ],
        'ui' => new class {
            public function notification($msg, $success)
            {
                echo "<div class='notification'>{$msg}</div>";
            }
            public function menu($label, $menu)
            {
                echo "<nav>Menu</nav>";
            }
            public function footer($label, $footer)
            {
                echo "<footer>Footer</footer>";
            }
        }
    ];
});

it('renders the correct title and language', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<html lang="en">');
    expect($output)->toContain('<title>' . Html::title('Test Page') . '</title>');
});

it('renders notification message', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('Test message');
});

it('renders the table headers', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    foreach (['ID', 'Description', 'Amount', 'Active', 'Actions'] as $header) {
        expect($output)->toContain("<th>{$header}</th>");
    }
});

it('renders all rows correctly', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('Category 1');
    expect($output)->toContain('Category 2');
    expect($output)->toContain('✓'); // active row
    expect($output)->toContain('–'); // inactive row
    expect($output)->toContain('<a href="/edit/1"');
    expect($output)->toContain('<a href="/edit/2"');
});

it('renders footer and menu', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('Menu');
    expect($output)->toContain('Footer');
});

it('renders disabled span when row has no valid id or href', function () {
    $this->data['rows'][] = ['id' => 0, 'description' => 'No Edit', 'amount' => 0, 'active' => false, 'parentId' => 0];

    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<span class="disabled">–</span>');
});
