<?php

namespace PHPLedger\Views;

/**
 * View for account object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

use PHPLedger\Domain\Account;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Html;
use PHPLedger\Views\ViewFactory;

class AccountView extends ObjectViewer
{
    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->object->id) || !($this->object instanceof account)) {
            return $retval;
        }
        /**
         * @var account $object
         */
        $object = $this->object;
        $type_description = "";
        if (!empty($object->typeId)) {
            $account_type = ObjectFactory::accounttype();
            $account_type = $account_type->getById($object->typeId);
            if (null === $account_type) {
                $account_type = ObjectFactory::accounttype();
            }
            $type_description = $account_type->description;
        }
        $retval .= "<td data-label='ID' class=\"number\"><a title=\"Editar\" href=\"accounts.php?id={$object->id}\">{$object->id}</a></td>";
        $retval .= "<td data-label='Nome' class=\"text\">{$object->name}</td>";
        $retval .= "<td data-label='Numero' class=\"number\">{$object->number}</td>";
        $retval .= "<td data-label='Tipo'>{$type_description}</td>";
        $retval .= "<td data-label='IBAN'>{$object->iban}</td>";
        $retval .= "<td data-label='Abertura'>{$object->openDate}</td>";
        $retval .= "<td data-label='Fecho'>{$object->closeDate}</td>";
        $retval .= "<td data-label='Activa'>" . ($object->activa ? "Sim" : "N&atilde;o") . "</td>";
        $retval .= "<td><a href=\"accounts.php?update=Apagar&amp;id={$object->id}\" onclick=\"return confirm('Pretende apagar o registo?');\">Apagar</a></td>";
        return $retval;
    }
    public function printForm(): string
    {
        $retval = "";
        if (!($this->object instanceof Account)) {
            return $retval;
        }
        /**
         * @var Account $object
         */
        $object = ($this->object);
        $id = isset($object->id) ? $object->id : $object->getNextId();
        $account_type = ObjectFactory::accounttype();
        if (isset($object->typeId)) {
            $account_type = $account_type->getById($object->typeId);
        }
        $accountTypeView = ViewFactory::instance()->accountTypeView($this->app, $account_type);
        $tipo_opt = $accountTypeView->getSelectFromList($account_type->getList(), isset($object->typeId) ? $object->typeId : null);
        $retval .= "<td data-label='ID'><input type=\"hidden\" name=\"id\" value=\"{$id}\">{$id}</td>\n";
        $retval .= "<td data-label='Nome'><a id=\"{$id}\"></a><input type=text size=16 maxlength=30 name=\"name\" value=\"{$object->name}\"></td>";
        $retval .= "<td data-label='Numero'><input type=text size=15 maxlength=30 name=\"number\" value=\"{$object->number}\"></td>";
        $retval .= "<td data-label='Tipo'><select name=\"typeId\">{$tipo_opt}</select>";
        $retval .= "<td data-label='NIB'><input type=text size=24 maxlength=24 name=\"iban\" value=\"{$object->iban}\"></td>";
        $retval .= "<td data-label='Abertura'>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaAA\">" . Html::yearOptions(isset($object->openDate) ? substr($object->openDate, 0, 4) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaMM\">" . Html::monthOptions(isset($object->openDate) ? substr($object->openDate, 5, 2) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaDD\">" . Html::dayOptions(isset($object->openDate) ? substr($object->openDate, 8, 2) : null) . "</select>\r\n";
        $retval .= "<input class=\"date-fallback\" type=\"date\" name=\"abertura\" required value=\"" . (isset($object->closeDate) ? $object->openDate : date("Y-m-d")) . "\">\r\n";
        $retval .= "</td>\r\n";
        $retval .= "<td data-label='Fecho'>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoAA\">" . Html::yearOptions(isset($object->closeDate) ? substr($object->closeDate, 0, 4) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoMM\">" . Html::monthOptions(isset($object->closeDate) ? substr($object->closeDate, 5, 2) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoDD\">" . Html::dayOptions(isset($object->closeDate) ? substr($object->closeDate, 8, 2) : null) . "</select>\r\n";
        $retval .= "<input class=\"date-fallback\" type=\"date\" name=\"fecho\" required value=\"" . (isset($object->closeDate) ? $object->closeDate : date("Y-m-d")) . "\">\r\n";
        $retval .= "</td>\r\n";
        $retval .= "<td data-label='Activa'><input  type=\"checkbox\" name=\"activa\" " . ((isset($object->activa) && ($object->activa == 1)) || empty($object->id) ? "checked" : "") . "></td>\r\n";
        $retval .= "<td><input class=\"submit\" type=\"submit\" name=\"update\" value=Gravar></td>";
        return $retval;
    }
    public function printObjectList(array $objectList): string
    {
        $retval = "<table class=\"lista contas account\">\r\n";
        $retval .= "<thead><tr><th>ID<th>Nome<th>Numero<th>Tipo<th>NIB<th>Abertura<th>Fecho<th>Activa<th>Apagar</tr></thead>";
        $retval .= "<tbody>\r\n";
        foreach ($objectList as $object) {
            if ($object instanceof account) {
                $view = new accountView($this->app, $object);
                $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
            }
        }
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $objectList, ?int $selected = null): string
    {
        $retval = "";
        /**
         * @var account $object
         */
        $object = $this->object;
        if (null === $selected) {
            $selected = $object->id;
        }
        foreach ($objectList as $object) {
            if ($object instanceof account) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->name}</option>\r\n";
            }
        }
        return $retval;
    }
}
