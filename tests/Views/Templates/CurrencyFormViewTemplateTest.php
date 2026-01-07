<?php

use PHPLedger\Views\Templates\CurrencyFormViewTemplate;
use PHPLedgerTests\Support\FakeUI;

/* minimal Html static calls */
if (!class_exists('PHPLedger\Util\Html')) {
    class Html {
        public static function title($page, $app) { return "$page - $app"; }
        public static function header() { echo '<meta charset="utf-8">'; }
    }
}

it('renders currency form with expected fields and values', function () {
    $view = new CurrencyFormViewTemplate();

    $data = [
        'lang' => 'en',
        'pagetitle' => 'Currency',
        'appTitle' => 'PHPLedger',
        'csrf' => '<input type="hidden" name="_csrf" value="token">',
        'ui' => new FakeUI(),
        'menu' => [],
        'footer' => [],
        'notification' => '',
        'success' => false,
        'label' => [
            'back_to_list' => 'Back',
            'id' => 'ID',
            'code' => 'Code',
            'description' => 'Description',
            'exchangeRate' => 'Exchange Rate',
            'save' => 'Save',
            'delete' => 'Delete',
            'are_you_sure_you_want_to_delete' => 'Confirm delete'
        ],
        'row' => [
            'id' => 1,
            'code' => 'EUR',
            'description' => 'Euro',
            'exchangeRate' => '1.00000000'
        ],
    ];

    ob_start();
    $view->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<form method="POST" action="index.php?action=currency&id=1">')
        ->toContain('name="_csrf"')
        ->toContain('name="code" value="EUR"')
        ->toContain('name="description" value="Euro"')
        ->toContain('name="exchangeRate"')
        ->toContain('value="1.00000000"')
        ->toContain('name="update" value="save"')
        ->toContain('name="update" value="delete"')
        ->toContain('Confirm delete');
});
