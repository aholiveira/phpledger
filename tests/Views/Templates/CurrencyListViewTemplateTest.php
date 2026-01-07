<?php

use PHPLedger\Views\Templates\CurrencyListViewTemplate;
use PHPLedgerTests\Support\FakeUI;

/* minimal Html static calls */
if (!class_exists('PHPLedger\Util\Html')) {
    class Html {
        public static function title($page, $app) { return "$page - $app"; }
        public static function header() { echo '<meta charset="utf-8">'; }
    }
}

it('renders currency list with rows and action links', function () {
    $view = new CurrencyListViewTemplate();

    $data = [
        'lang' => 'en',
        'pagetitle' => 'Currencies',
        'appTitle' => 'PHPLedger',
        'ui' => new FakeUI(),
        'menu' => [],
        'footer' => [],
        'label' => [
            'add' => 'Add',
            'actions' => 'Actions',
            'code' => 'Code',
            'description' => 'Description',
            'exchangeRate' => 'Exchange Rate',
            'edit' => 'Edit',
        ],
        'rows' => [
            [
                'id' => 1,
                'code' => 'EUR',
                'description' => 'Euro',
                'exchangeRate' => '1.00000000',
            ],
            [
                'id' => 2,
                'code' => 'USD',
                'description' => 'US Dollar',
                'exchangeRate' => '1.08000000',
            ],
        ],
    ];

    ob_start();
    $view->render($data);
    $html = ob_get_clean();

    expect($html)
        ->toContain('<table class="lista currency">')
        ->toContain('index.php?action=currency&lang=en')
        ->toContain('index.php?action=currency&id=1')
        ->toContain('index.php?action=currency&id=2')
        ->toContain('EUR')
        ->toContain('Euro')
        ->toContain('1.00000000')
        ->toContain('USD')
        ->toContain('US Dollar')
        ->toContain('1.08000000')
        ->toContain('id="preloader"')
        ->toContain('setTimeout');
});
