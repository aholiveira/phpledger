<?php

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\AccountTypeListViewTemplate;

final class AccountTypeListController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = $this->app->dataFactory()::accounttype();
        $list = $object->getList();
        $rows = [];
        foreach ($list as $row) {
            $rows[] = [
                'id' => $row->id,
                'description' => $row->description,
                'savings' => $row->savings,
            ];
        }
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels($this->app->l10n(), ['description', 'savings', 'actions'])
        );
        $view = new AccountTypeListViewTemplate();
        $view->render(array_merge($this->uiData, [
            'rows' => $rows,
            'pagetitle' => $this->app->l10n()->l("account_types")
        ]));
    }
}
