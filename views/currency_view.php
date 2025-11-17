<?php

use \PHPLedger\Contracts\DataObjectInterface;

/**
 * View for Currency object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use \PHPLedger\Domain\Currency;
class currency_view extends ObjectViewer
{
    protected currency $object;
    public function __construct(currency $object)
    {
        parent::__construct($object);
    }
    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->_object->id)) {
            return $retval;
        }
        $_object = $this->_object;
        if (!($_object instanceof currency))
            return $retval;
        $retval .= "<td><a title=\"Editar\" href=\"currency.php?tipo_id={$_object->id}\">{$_object->id}</a></td>";
        $retval .= "<td>{$_object->code}</td>";
        $retval .= "<td>{$_object->description}</td>";
        $retval .= "<td style=\"text-align: right\">" . normalize_number($_object->exchange_rate) . "</td>";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista currency\">\n";
        $retval .= "<thead><tr><th>ID</th><th>C&oacute;digo</th><th>Nome</th><th>C&acirc;mbio</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof currency) {
                $view = new currency_view($object);
                $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
            }
        }
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\r\n";
        return $retval;
    }
    public function printForm(): string
    {
        $retval = "";
        $_object = $this->_object;
        if (!$_object instanceof currency) {
            return $retval;
        }
        $retval .= "<tr>";
        $retval .= "<td><label for=\"id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"id\" value=" . (isset($_object->id) ? $_object->id : $_object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"code\">ID</label></td>\r\n";
        $retval .= "<td><input type=text size=4 name=\"code\" value=" . (isset($_object->code) ? $_object->code : "") . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"description\">Nome</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"description\" value=\"" . (isset($_object->id) ? $_object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"exchange_rate\">C&acirc;mbio</label></td>\n";
        $retval .= "<td><input type=number name=\"exchange_rate\" value=\"" . (isset($_object->id) ? $_object->exchange_rate : "") . "\"></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $object_list, ?string $selected = null): string
    {
        $retval = "";
        /**
         * @var currency $_object
         */
        $_object = $this->_object;
        if (null === $selected) {
            $selected = $_object->id;
        }
        foreach ($object_list as $object) {
            if (($object instanceof currency)) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->code} - {$object->description}</option>\r\n";
            }
        }
        return $retval;
    }
}
