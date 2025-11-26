<?php

/**
 * View for account_type class
 * Contains methods needed to view an account_type object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
namespace PHPLedger\Views;

use PHPLedger\Domain\AccountType;
class AccountTypeView extends ObjectViewer
{

    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->object->id)) {
            return $retval;
        }
        $object = $this->object;
        if (!($object instanceof accounttype)) {
            return $retval;
        }
        $retval .= "<td data-label='ID' id=\"{$object->id}\"><a title=\"Editar\" href=\"account_types.php?id={$object->id}\">{$object->id}</a></td>";
        $retval .= "<td data-label='Descri&ccedil;&atilde;o'>{$object->description}</td>";
        $retval .= "<td data-label='Savings?' class=\"checkbox\"><input type=\"checkbox\" onclick=\"return false;\" name=savings{$object->id} " . ($object->savings ? "checked" : "") . "></td>\n";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista account_type\">\r\n";
        $retval .= "<thead><tr><th>ID</th><th>Descri&ccedil;&atilde;o</th><th>Savings?</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof accounttype) {
                $view = new accountTypeView($object);
                $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
            }
        }
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $object_list, ?int $selected = null): string
    {
        $retval = "";
        /**
         * @var accounttype $object
         */
        $object = $this->object;
        if (null === $selected) {
            $selected = $object->id;
        }
        foreach ($object_list as $object) {
            if (($object instanceof accounttype)) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->description}</option>\r\n";
            }
        }
        return $retval;
    }
}
