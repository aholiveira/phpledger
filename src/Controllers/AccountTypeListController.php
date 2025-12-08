<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\AccountTypeListView;

final class AccountTypeListController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = ObjectFactory::accounttype();
        $list = $object->getList();
        $view = new AccountTypeListView();
        $view->render($this->app, ['list' => $list, 'lang' => $this->app->l10n()->lang(), 'action' => $this->request->input('action')]);
    }
}
