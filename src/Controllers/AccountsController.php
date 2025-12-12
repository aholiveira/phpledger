<?php

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\AccountListViewTemplate;

final class AccountsController extends AbstractViewController
{
    /**
     * Handle list request (GET)
     *
     * @return void
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
                'activa' => !empty($r->activa)
            ];
        }
        $l10n = $this->app->l10n();
        $this->uiData['label'] = array_merge($this->uiData['label'], $this->buildL10nLabels($l10n, [
            'add',
            'id',
            'name',
            'number',
            'type',
            'iban',
            'swift',
            'open',
            'close',
            'active',
            'yes',
            'no',
            'actions',
            'edit'
        ]));
        $view = new AccountListViewTemplate();
        $view->render(array_merge($this->uiData, [
            'list' => $list,
            'action' => $this->request->input('action', 'accounts'),
            'pagetitle' => $this->app->l10n()->l('accounts')
        ]));
    }
}
