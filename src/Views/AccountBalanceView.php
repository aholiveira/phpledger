<?php

/**
 * Balances view for account object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Views;

use PHPLedger\Domain\Account;
use PHPLedger\Util\L10n;
use PHPLedger\Util\NumberUtil;
class AccountBalanceView extends ObjectViewer
{
    public function printObject(): string
    {
        if (!isset($this->object->id)) {
            return "";
        }
        /**
         * @var account
         */
        $object = $this->object;
        return "<td><a href=\"account_types.php?id={$object->id}\">{$object->id}</a></td>"
            . "<td>{$object->name}</td>";
    }

    public function printObjectList(array $objectList): string
    {
        $retval = "<table class=\"lista saldos\">\r\n";
        $retval .= "<thead><tr>"
            . "<th>" . l10n::l('account') . "</th>"
            . "<th>" . l10n::l('deposits') . "</th>"
            . "<th>" . l10n::l('withdrawals') . "</th>"
            . "<th>" . l10n::l('balance') . "</th>"
            . "<th>" . l10n::l('percent') . "</th>"
            . "<th>" . l10n::l('entries') . "</th>"
            . "</tr></thead>\r\n";
        $retval .= "<tbody>";

        $type_names = ['income', 'expense', 'balance'];
        $totals = array_fill_keys($type_names, 0);
        $balances = [];

        foreach ($objectList as $object) {
            if ($object instanceof account) {
                $balances[$object->id] = $object->getBalanceOnDate(new \DateTime());
                foreach ($type_names as $type_name) {
                    $totals[$type_name] += $balances[$object->id][$type_name];
                }
            }
        }

        foreach ($objectList as $object) {
            if ($object instanceof account) {
                $balance = $balances[$object->id];
                $retval .= "<tr>";
                $retval .= "<td class='account' data-label='" . l10n::l('account') . "'>"
                    . "<a title='" . l10n::l('edit_account') . "' href='index.php?action=account&id={$object->id}'>"
                    . "{$object->name}</a></td>";
                $retval .= "<td class='deposits' data-label='" . l10n::l('deposits') . "'>"
                    . NumberUtil::normalize($balance['income']) . "</td>";
                $retval .= "<td class='withdrawls' data-label='" . l10n::l('withdrawals') . "'>"
                    . NumberUtil::normalize($balance['expense']) . "</td>";
                $retval .= "<td class='balance' data-label='" . l10n::l('balance') . "'>"
                    . NumberUtil::normalize($balance['balance']) . "</td>";
                $retval .= "<td class='percent' data-label='" . l10n::l('percent') . "'>"
                    . NumberUtil::normalize($totals['balance'] <> 0 ? round($balance['balance'] / $totals['balance'] * 100, 2) : 0)
                    . "</td>";
                $retval .= "<td class='entries-list' data-label='" . l10n::l('entries') . "'>"
                    . "<a title='" . l10n::l('account_entries') . "' href='index.php?action=ledger_entries&filter_accountId={$object->id}'>"
                    . l10n::l('list') . "</a></td>";
                $retval .= "</tr>\r\n";
            }
        }

        // Networth row
        $retval .= "<tr>";
        $retval .= "<td class='account' data-label='" . l10n::l('account') . "'>" . l10n::l('networth') . "</td>";
        $retval .= "<td class='deposits' data-label='" . l10n::l('deposits') . "'>" . NumberUtil::normalize($totals['income']) . "</td>";
        $retval .= "<td class='withdrawls' data-label='" . l10n::l('withdrawals') . "'>" . NumberUtil::normalize($totals['expense']) . "</td>";
        $retval .= "<td class='balance' data-label='" . l10n::l('balance') . "'>" . NumberUtil::normalize($totals['balance']) . "</td>";
        $retval .= "<td class='percent' data-label='" . l10n::l('percent') . "'>" . NumberUtil::normalize(100) . "</td>";
        $retval .= "<td class='entries-list' data-label='" . l10n::l('entries') . "'></td>";
        $retval .= "</tr>\r\n";

        $retval .= "</tbody>\r\n</table>\r\n";
        return $retval;
    }
}
