<?php

/**
 * Controller for listing account types.
 *
 * Fetches all account types and renders them using the account type list view template.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\AccountTypeListViewTemplate;

final class AccountTypeListController extends AbstractViewController
{
    /**
     * Handle account type list request (GET).
     */
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

        $view = new AccountTypeListViewTemplate();
        $view->render(array_merge($this->uiData, [
            'rows' => $rows,
            'pagetitle' => $this->app->l10n()->l("account_types")
        ]));
    }
}
