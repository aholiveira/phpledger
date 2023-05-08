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
class account_type_view extends object_viewer
{

    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->_object->id)) {
            return $retval;
        }
        $_object = $this->_object;
        if (!($_object instanceof accounttype)) return $retval;
        $retval .= "<td data-label='ID' id=\"{$_object->id}\"><a title=\"Editar\" href=\"account_types.php?tipo_id={$_object->id}\">{$_object->id}</a></td>";
        $retval .= "<td data-label='Descri&ccedil;&atilde;o'>{$_object->description}</td>";
        $retval .= "<td data-label='Savings?' class=\"checkbox\"><input type=\"checkbox\" readonly onclick=\"return false;\" name=savings{$_object->id} " . ($_object->savings ? "checked" : "") . "></td>\n";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista account_type\">\r\n";
        $retval .= "<thead><tr><th>ID</th><th>Descri&ccedil;&atilde;o</th><th>Savings?</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof accounttype) {
                $view = new account_type_view($object);
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
        $_object = $this->_object;
        if (is_null($selected)) $selected = $_object->id;
        foreach ($object_list as $object) {
            if (($object instanceof accounttype)) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->description}</option>\r\n";
            }
        }
        return $retval;
    }
}
