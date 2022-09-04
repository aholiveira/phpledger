<?php

/**
 * Balances view for account object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class account_balance_view extends object_viewer
{
    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->_object->id)) {
            return $retval;
        }
        $_object = $this->_object;
        $retval .= "<td><a href=\"tipo_contas.php?tipo_id={$_object->id}\">{$_object->id}</a></td>";
        $retval .= "<td>{$_object->description}</td>";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista saldos\">\r\n";
        $retval .= "<thead><tr><th>Conta</th><th>Depositos</th><th>Levantam.</th><th>Saldo</th><th>% do total</th><th>Movimentos</th></tr></thead>\r\n";
        $retval .= "<tbody>";
        $type_names = array('income', 'expense', 'balance');
        foreach ($type_names as $type_name) {
            $totals[$type_name] = 0;
        }
        foreach ($object_list as $object) {
            if ($object instanceof account) {
                $balances[$object->id] = $object->getBalanceOnDate(new DateTime());
                foreach ($type_names as $type_name) {
                    $totals[$type_name] += $balances[$object->id][$type_name];
                }
            }
        }
        foreach ($object_list as $object) {
            if ($object instanceof account) {
                $balance = $balances[$object->id];
                $retval .= "<tr>";
                $retval .= "<td class='account' data-label='Conta'><a title=\"Editar esta conta\" href=\"contas.php?conta_id={$object->id}\">{$object->name}</a></td>";
                $retval .= "<td class='deposits' data-label='Depositos'>" . normalize_number($balance['income']) . "</td>";
                $retval .= "<td class='withdrawls' data-label='Levantam.'>" . normalize_number($balance['expense']) . "</td>";
                $retval .= "<td class='balance' data-label='Saldo'>" . normalize_number($balance['balance']) . "</td>";
                $retval .= "<td class='percent' data-label='Percentagem'>" . normalize_number(round($balance['balance'] / $totals['balance'] * 100, 2)) . "</td>";
                $retval .= "<td class='entries-list' data-label='Movimentos'><a title=\"Movimentos desta conta\" href=\"ledger_entries.php?conta_id={$object->id}\">Lista</a></td>";
                $retval .= "</tr>\r\n";
            }
        }
        $retval .= "<tr>";
        $retval .= "<td class='account' data-label='Conta'>Networth</td>";
        $retval .= "<td class='deposits' data-label='Depositos'>" . normalize_number($totals['income']) . "</td>";
        $retval .= "<td class='withdrawls' data-label='Levantam.'>" . normalize_number($totals['expense']) . "</td>";
        $retval .= "<td class='balance' data-label='Saldo'>" . normalize_number($totals['balance']) . "</td>";
        $retval .= "<td class='percent' data-label='Percentagem'>" . normalize_number(100) . "</td>";
        $retval .= "<td class='entries-list' data-label='Movimentos'></td>";
        $retval .= "</tr>\r\n";
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\r\n";
        return $retval;
    }
}
