<?php

/**
 * View for a ledger_entry
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class ledger_entry_view extends object_viewer
{
    public function printObject(): string
    {
        $retval = "";
        if (empty($this->_object->id))
            return $retval;
        $retval .= "<td data-label='ID'><a id=\"{$this->_object->id}\" title=\"Editar\" href=\"ledger_entries.php?id={$this->_object->id}#{$this->_object->id}\">{$this->_object->id}</a></td>\n";
        $retval .= "<td data-label='Data' style=\"text-align: center\">{$this->_object->entry_date}</td>\n";
        $retval .= "<td data-label='Categoria'><a title=\"Mostrar movimentos apenas desta categoria\" href=\"ledger_entries.php?filter_tipo_mov={$this->_object->category->id}\">{$this->_object->category->description}</a></td>\n";
        $retval .= "<td data-label='Moeda'>{$this->_object->category->description}</td>\n";
        $retval .= "<td data-label='Conta'><a title=\"Mostrar movimentos apenas desta conta\" href=\"ledger_entries.php?filter_account_id={$this->_object->account->id}\">{$this->_object->account->name}</a></td>\n";
        $retval .= "<td data-label='D/C'>" . ($this->_object->direction == "1" ? "Dep" : "Lev") . "</td>\n";
        $retval .= "<td data-label='Valor' class='amount'>" . normalize_number($this->_object->currency_amount) . "</td>\n";
        $retval .= "<td data-label='Obs'>{$this->_object->remarks}</td>\n";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista ledger_entry\">\r\n";
        $retval .= "<thead><tr><th>ID</th><th>Data</th><th>Categoria</th><th>Moeda</th><th>Conta</th><th>D/C</th><th>Valor</th><th>Obs</th><th>Saldo</th></tr></thead>\r\n";
        $object = $object_list[array_key_first($object_list)];
        if ($object instanceof ledgerentry) {
            $saldo = $object->getBalanceBeforeDate($object->entry_date);
        }
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof ledgerentry) {
                $saldo += $object->euro_amount;
                if ($object->id == $this->_object->id) {
                    //$this->printForm();
                } else {
                    $view = new ledger_entry_view($object);
                    $retval .= "<tr>" . $view->printObject();
                }
                $retval .= "<td data-label='Saldo' class='total'>" . normalize_number($saldo) . "</td>\r\n";
                $retval .= "</tr>\r\n";
            }
        }
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\r\n";
        return $retval;
    }
    /*public function printForm(): string
    {
        $retval = "";
        $_object = $this->_object;
        if (!$_object instanceof ledgerentry) return $retval;
        $retval .= "<tr>";
        $retval .= "<td><label for=\"id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"id\" value=" . (isset($_object->id)  ? $_object->id : $_object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"tipo_desc\">Nome</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"name\" value=\"" . (isset($_object->id) ? $_object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"active\">C&acirc;mbio</label></td>\n";
        $retval .= "<td><input type=\"checkbox\" name=\"active\" " . ((isset($_object->id) && $_object->active) || !isset($_object->id) ? "checked" : "") . "></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }*/
}
