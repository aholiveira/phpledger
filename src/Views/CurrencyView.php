<?php
namespace PHPLedger\Views;

/**
 * View for Currency object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Domain\Currency;
class CurrencyView extends ObjectViewer
{
    public function __construct(currency $object)
    {
        parent::__construct($object);
    }
    public function printObject(): string
    {
        $retval = "";
        if (!isset($this->object->id)) {
            return $retval;
        }
        $object = $this->object;
        if (!($object instanceof currency)) {
            return $retval;
        }
        $retval .= "<td><a title=\"Editar\" href=\"currency.php?id={$object->id}\">{$object->id}</a></td>";
        $retval .= "<td>{$object->code}</td>";
        $retval .= "<td>{$object->description}</td>";
        $retval .= "<td style=\"text-align: right\">" . normalizeNumber($object->exchangeRate) . "</td>";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista currency\">\n";
        $retval .= "<thead><tr><th>ID</th><th>C&oacute;digo</th><th>Nome</th><th>C&acirc;mbio</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        foreach ($object_list as $object) {
            if ($object instanceof currency) {
                $view = new currencyView($object);
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
        $object = $this->object;
        if (!$object instanceof currency) {
            return $retval;
        }
        $retval .= "<tr>";
        $retval .= "<td><label for=\"id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"id\" value=" . (isset($object->id) ? $object->id : $object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"code\">ID</label></td>\r\n";
        $retval .= "<td><input type=text size=4 name=\"code\" value=" . (isset($object->code) ? $object->code : "") . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"description\">Nome</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"description\" value=\"" . (isset($object->id) ? $object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"exchangeRate\">C&acirc;mbio</label></td>\n";
        $retval .= "<td><input type=number name=\"exchangeRate\" value=\"" . (isset($object->id) ? $object->exchangeRate : "") . "\"></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $object_list, ?string $selected = null): string
    {
        $retval = "";
        /**
         * @var currency $object
         */
        $object = $this->object;
        if (null === $selected) {
            $selected = $object->id;
        }
        foreach ($object_list as $object) {
            if (($object instanceof currency)) {
                $retval .= "<option value=\"{$object->id}\"" . ($selected == $object->id ? " selected " : "") . ">{$object->code} - {$object->description}</option>\r\n";
            }
        }
        return $retval;
    }
}
