<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\AccountListViewTemplate;

/**
 * Controller for listing accounts.
 *
 * Fetches accounts and their types, prepares data for display,
 * and renders the account list view template.
 *
 */
final class AccountsController extends AbstractViewController
{
    /**
     * Handle list request (GET).
     */
    protected function handle(): void
    {
        $acc = $this->app->dataFactory()::account()::getList();
        $types = $this->app->dataFactory()::accountType()::getList();
        $cache = [];
        foreach ($types as $t) {
            $cache[$t->id] = $t->description ?? '';
        }
        $list = [];
        foreach ($acc as $r) {
            $list[] = [
                'id' => $r->id ?? '',
                'name' => $r->name ?? '',
                'number' => $r->number ?? '',
                'type' => ($r->typeId && isset($cache[$r->typeId])) ? $cache[$r->typeId] : '',
                'iban' => $r->iban ?? '',
                'swift' => $r->swift ?? '',
                'openDate' => $r->openDate ?? '',
                'closeDate' => $r->closeDate ?? '',
                'active' => !empty($r->active)
            ];
        }
        $view = new AccountListViewTemplate();
        $view->render(array_merge($this->uiData, [
            'list' => $list,
            'action' => $this->request->input('action', 'accounts'),
            'pagetitle' => $this->app->l10n()->l('accounts')
        ]));
    }
}
