<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\AccountListView;

final class AccountsController extends AbstractViewController
{
    /**
     * Handle list request (GET)
     *
     * @return void
     */
    protected function handle(): void
    {
        $list = ObjectFactory::account()::getList();
        $view = new AccountListView();
        $view->render($this->app, [
            'list' => $list,
            'lang' => $this->app->l10n()->sanitizeLang($_GET['lang'] ?? null),
            'action' => $this->request->input('action', 'accounts')
        ]);
    }
}
