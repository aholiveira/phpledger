<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\L10n;
use PHPLedger\Views\AccountTypeListView;
use PHPLedger\Util\SessionManager;

final class AccountTypeListController
{
    public function handle(): void
    {
        SessionManager::start();

        $object = ObjectFactory::accounttype();
        $list = $object->getList();

        $view = new AccountTypeListView();
        $view->render(['list' => $list, 'lang' => L10n::$lang]);
    }
}
