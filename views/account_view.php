<?php

/**
 * View for account object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
class account_view extends object_viewer
{
    public function printObject(): string
    {
        global $object_factory;
        $retval = "";
        if (!isset($this->_object->id) || !($this->_object instanceof account)) return $retval;
        /**
         * @var account $_object
         */
        $_object = $this->_object;
        $type_description = "";
        if (!empty($_object->type_id)) {
            $account_type = $object_factory->accounttype();
            $account_type->getById($_object->type_id);
            $type_description = $account_type->description;
        }
        $retval .= "<td data-label='ID' class=\"number\"><a title=\"Editar\" href=\"accounts.php?conta_id={$_object->id}\">{$_object->id}</a></td>";
        $retval .= "<td data-label='Nome' class=\"text\">{$_object->name}</a></td>";
        $retval .= "<td data-label='Numero' class=\"number\">{$_object->number}</a></td>";
        $retval .= "<td data-label='Tipo'>{$type_description}</a></td>";
        $retval .= "<td data-label='IBAN'>{$_object->iban}</a></td>";
        $retval .= "<td data-label='Abertura'>{$_object->open_date}</a></td>";
        $retval .= "<td data-label='Fecho'>{$_object->close_date}</td>";
        $retval .= "<td data-label='Activa'>" . ($_object->active ? "Sim" : "N&atilde;o") . "</td>";
        $retval .= "<td><a href=\"accounts.php?update=Apagar&amp;conta_id={$_object->id}\" onclick=\"return confirm('Pretende apagar o registo?');\">Apagar</a></td>";
        return $retval;
    }
    public function printForm(): string
    {
        global $object_factory;
        global $view_factory;

        $retval = "";
        if (!($this->_object instanceof account)) return $retval;
        /**
         * @var account $_object
         */
        $_object = ($this->_object);
        $id = isset($_object->id) ? $_object->id : $_object->getNextId();
        $account_type = $object_factory->accounttype();
        if (isset($_object->type_id)) {
            $account_type->getById($_object->type_id);
        }
        $account_type_view = $view_factory->account_type_view($account_type);
        $tipo_opt = $account_type_view->getSelectFromList($account_type->getList(), isset($_object->type_id) ? $_object->type_id : null);
        $retval .= "<td data-label='ID'><input type=\"hidden\" name=\"conta_id\" value=\"{$id}\"/>{$id}</td>\n";
        $retval .= "<td data-label='Nome'><a name=\"{$id}\"><input type=text size=16 maxlength=30 name=\"conta_nome\" value=\"{$_object->name}\"></a></td>";
        $retval .= "<td data-label='Numero'><input type=text size=15 maxlength=30 name=\"conta_num\" value=\"{$_object->number}\"></td>";
        $retval .= "<td data-label='Tipo'><select name=\"tipo_id\">{$tipo_opt}</select>";
        $retval .= "<td data-label='NIB'><input type=text size=24 maxlength=24 name=\"conta_nib\" value=\"{$_object->iban}\"></td>";
        $retval .= "<td data-label='Abertura'>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaAA\">" . Html::year_option(isset($_object->open_date) ? substr($_object->open_date, 0, 4) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaMM\">" . Html::mon_option(isset($_object->open_date) ? substr($_object->open_date, 5, 2) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"aberturaDD\">" . Html::day_option(isset($_object->open_date) ? substr($_object->open_date, 8, 2) : null) . "</select>\r\n";
        $retval .= "<input class=\"date-fallback\" type=\"date\" name=\"abertura\" required value=\"" . (isset($_object->close_date) ? $_object->open_date : date("Y-m-d")) . "\">\r\n";
        $retval .= "</td>\r\n";
        $retval .= "<td data-label='Fecho'>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoAA\">" . Html::year_option(isset($_object->close_date) ? substr($_object->close_date, 0, 4) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoMM\">" . Html::mon_option(isset($_object->close_date) ? substr($_object->close_date, 5, 2) : null) . "</select>\r\n";
        $retval .= "<select class=\"date-fallback\" style=\"display: none\" name=\"fechoDD\">" . Html::day_option(isset($_object->close_date) ? substr($_object->close_date, 8, 2) : null) . "</select>\r\n";
        $retval .= "<input class=\"date-fallback\" type=\"date\" name=\"fecho\" required value=\"" . (isset($_object->close_date) ? $_object->close_date : date("Y-m-d")) . "\">\r\n";
        $retval .= "</td>\r\n";
        $retval .= "<td data-label='Activa'><input  type=\"checkbox\" name=\"activa\" " . ((isset($_object->active) && ($_object->active == 1)) || empty($_object->id) ? "checked" : "") . "></td>\r\n";
        $retval .= "<td><input class=\"submit\" type=\"submit\" name=\"update\" value=Gravar></td>";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista contas account\">\r\n";
        $retval .= "<thead><tr><th>ID<th>Nome<th>Numero<th>Tipo<th>NIB<th>Abertura<th>Fecho<th>Activa<th>Apagar</tr></thead>";
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof account) {
                $view = new account_view($object);
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
            if (($object instanceof account)) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->name}</option>\r\n";
            }
        }
        return $retval;
    }
}
