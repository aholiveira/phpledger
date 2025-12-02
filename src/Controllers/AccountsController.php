<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\L10n;
use PHPLedger\Util\SessionManager;
use PHPLedger\Views\AccountListView;

final class AccountsController
{
    /**
     * Handle list request (GET)
     *
     * @return void
     */
    public function handle(): void
    {
        SessionManager::start();
        $list = ObjectFactory::account()::getList();
        $view = new AccountListView();
        $view->render([
            'list' => $list,
            'lang' => L10n::sanitizeLang($_GET['lang'] ?? null)
        ]);
    }
}
