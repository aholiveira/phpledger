<?php

/**
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\CurrencyListViewTemplate;

/**
 * Controller for listing currencies.
 *
 * Fetches all currencies and renders them using the account type list view template.
 */
final class CurrencyListController extends AbstractViewController
{
    /**
     * Handle currency list request.
     */
    protected function handle(): void
    {
        $object = $this->app->dataFactory()::currency();
        $list = $object->getList();
        $rows = [];
        foreach ($list as $row) {
            $rows[] = [
                'id' => $row->id,
                'code' => $row->code,
                'description' => $row->description,
                'exchangeRate' => $row->exchangeRate,
                'username' => $row->username,
                'createdAt' => $row->createdAt,
                'updatedAt' => $row->updatedAt,
            ];
        }

        $view = new CurrencyListViewTemplate();
        $view->render(array_merge($this->uiData, [
            'rows' => $rows,
            'pagetitle' => $this->app->l10n()->l("currency")
        ]));
    }
}
