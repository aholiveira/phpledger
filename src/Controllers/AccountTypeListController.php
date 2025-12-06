<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\L10n;
use PHPLedger\Views\AccountTypeListView;

final class AccountTypeListController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = ObjectFactory::accounttype();
        $list = $object->getList();
        $view = new AccountTypeListView();
        $view->render(['list' => $list, 'lang' => L10n::$lang]);
    }
}
