<?php

/**
 * View for a ledger_entry
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use \PHPLedger\Contracts\DataObjectInterface;
use \PHPLedger\Domain\LedgerEntry;
class ledgerEntryView extends ObjectViewer
{
    /** @var ledgerentry $object */
    protected DataObjectInterface $object;
    public function printObject(): string
    {
        $retval = "";
        if (empty($this->object->id)) {
            return $retval;
        }
        $retval .= "<td data-label='ID'><a id=\"{$this->object->id}\" title=\"Editar\" href=\"ledger_entries.php?id={$this->object->id}#{$this->object->id}\">{$this->object->id}</a></td>\n";
        $retval .= "<td data-label='Data' style=\"text-align: center\">{$this->object->entry_date}</td>\n";
        $retval .= "<td data-label='Categoria'><a title=\"Mostrar movimentos apenas desta categoria\" href=\"ledger_entries.php?filter_tipo_mov={$this->object->category->id}\">{$this->object->category->description}</a></td>\n";
        $retval .= "<td data-label='Moeda'>{$this->object->currency->description}</td>\n";
        $retval .= "<td data-label='Conta'><a title=\"Mostrar movimentos apenas desta conta\" href=\"ledger_entries.php?filter_accountId={$this->object->account->id}\">{$this->object->account->name}</a></td>\n";
        $retval .= "<td data-label='D/C'>" . ($this->object->direction == "1" ? "Dep" : "Lev") . "</td>\n";
        $retval .= "<td data-label='Valor' class='amount'>" . normalize_number($this->object->currencyAmount) . "</td>\n";
        $retval .= "<td data-label='Obs'>{$this->object->remarks}</td>\n";
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
                $saldo += $object->euroAmount;
                if ($object->id == $this->object->id) {
                    //$this->printForm();
                } else {
                    $view = new ledgerEntryView($object);
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
        $object = $this->object;
        if (!$object instanceof ledgerentry) return $retval;
        $retval .= "<tr>";
        $retval .= "<td><label for=\"id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"id\" value=" . (isset($object->id)  ? $object->id : $object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"tipo_desc\">Nome</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"name\" value=\"" . (isset($object->id) ? $object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"active\">C&acirc;mbio</label></td>\n";
        $retval .= "<td><input type=\"checkbox\" name=\"active\" " . ((isset($object->id) && $object->active) || !isset($object->id) ? "checked" : "") . "></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }*/
}
