<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\Account;
use PHPLedger\Util\NumberUtil;
use PHPLedger\Views\Templates\BalancesViewTemplate;

final class BalancesController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = $this->app->dataFactory()->account();
        $objectList = $object->getList(['activa' => ['operator' => '=', 'value' => '1']]);
        $type_names = ['income', 'expense', 'balance'];
        $totals = array_fill_keys($type_names, 0);
        $balances = [];

        foreach ($objectList as $object) {
            if ($object instanceof Account) {
                $balances[$object->id] = $object->getBalanceOnDate(new \DateTime());
                foreach ($type_names as $type_name) {
                    $totals[$type_name] += $balances[$object->id][$type_name];
                }
            }
        }
        $l10n = [
            'account' => $this->app->l10n()->l('account'),
            'deposits' => $this->app->l10n()->l('deposits'),
            'withdrawals' => $this->app->l10n()->l('withdrawals'),
            'balance' => $this->app->l10n()->l('balance'),
            'percent' => $this->app->l10n()->l('percent'),
            'entries' => $this->app->l10n()->l('entries'),
            'edit_account' => $this->app->l10n()->l('edit_account'),
            'account_entries' => $this->app->l10n()->l('account_entries'),
            'list' => $this->app->l10n()->l('list')
        ];
        $rows = [];
        foreach ($objectList as $object) {
            if ($object instanceof Account) {
                $rows[] = [
                    'text' => [
                        'name' => $object->name,
                        'deposits' => NumberUtil::normalize($balances[$object->id]['income']),
                        'withdrawals' => NumberUtil::normalize($balances[$object->id]['expense']),
                        'balance' => NumberUtil::normalize($balances[$object->id]['balance']),
                        'percent' => NumberUtil::normalize($totals['balance'] <> 0 ? round($balances[$object->id]['balance'] / $totals['balance'] * 100, 2) : 0),
                    ],
                    'href' => [
                        'name' => "index.php?action=account&back=balances&id={$object->id}",
                        'entries' => "index.php?action=ledger_entries&filter_accountId={$object->id}"
                    ]
                ];
            }
        }
        $rows[] = [
            'text' => [
                'name' => $this->app->l10n()->l('networth'),
                'deposits' => NumberUtil::normalize($totals['income']),
                'withdrawals' => NumberUtil::normalize($totals['expense']),
                'balance' => NumberUtil::normalize($totals['balance']),
                'percent' =>  NumberUtil::normalize(100),
            ],
            'href' => ['name' => '', 'entries' => '']
        ];

        $templateData = [
            'title' => "Saldos",
            'lang' => $this->app->l10n()->html(),
            'app' => $this->app,
            'action' => $this->request->input('action', 'balances'),
            'isAdmin' => $this->app->session()->get('isAdmin', false),
            'rows' => $rows,
            'l10n' => $l10n,
            'totals' => $totals,
        ];
        $view = new BalancesViewTemplate;
        $view->render($templateData);
    }
}
