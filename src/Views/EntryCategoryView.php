<?php
namespace PHPLedger\Views;

/**
 * View for EntryCategory object
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

use PHPLedger\Domain\EntryCategory;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\NumberUtil;
class EntryCategoryView extends ObjectViewer
{
    public function __construct(EntryCategory $object)
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
        if (!($object instanceof EntryCategory)) {
            return $retval;
        }
        $retval .= "<td class='id' data-label='ID'><a href=\"entry_type.php?id={$object->id}\" title=\"Editar a categoria\">{$object->id}</a></td>";
        $retval .= "<td class='category' data-label='Categoria'>" . (null === $object->parentId || $object->parentId == 0 ? "" : ($object->parentDescription ?? "")) . "</td>";
        $retval .= "<td class='description' data-label='Descri&ccedil;&atilde;o'>{$object->description}</td>";
        $retval .= "<td class='amount' data-label='Valor'>" . NumberUtil::normalize(abs($object->getBalance())) . "</td>";
        $retval .= "<td class='active checkbox' data-label='Activa'><input title=\"S&oacute; pode alterar o estado em modo de edi&ccedil;&atilde;o\" type=\"checkbox\" onclick=\"return false;\" name=active{$object->id} " . ($object->active ? "checked" : "") . "></td>\r\n";
        return $retval;
    }
    public function printObjectList(array $objectList): string
    {
        $retval = "<table class=\"lista entry_category\">\r\n";
        $retval .= "<thead><tr><th>ID</th><th>Categoria</th><th>Descri&ccedil;&atilde;o</th><th>Valor</th><th>Activa</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        $view = new entryCategoryView(ObjectFactory::entryCategory());
        foreach ($objectList as $object) {
            if ($object instanceof EntryCategory) {
                if ($object->parentId === 0) {
                    $view->setObject($object);
                    $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
                    foreach ($object->children as $child) {
                        $view->setObject($child);
                        $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
                    }
                }
                if (!isset($object->parentId)) {
                    $view->setObject($object);
                    $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
                }
            }
        }
        $retval .= "</tbody>\r\n";
        $retval .= "</table>\n";
        return $retval;
    }
    public function printForm(): string
    {
        $retval = "";
        $object = $this->object;
        if (!$object instanceof EntryCategory) {
            return $retval;
        }
        if (isset($object->id)) {
            $filter = [
                'active' => ['operator' => '=', 'value' => '1'],
                'id' => ['operator' => '<>', 'value' => "{$object->id}"]
            ];
        } else {
            $filter = ['active' => ['operator' => '=', 'value' => '1']];
        }
        $category_list = $object->getList($filter);
        if (isset($object->id)) {
            foreach ($category_list as $key => $category) {
                if ($category->parentId == $object->id || $category->id == $object->id) {
                    unset($category_list[$key]);
                }
            }
        }
        $retval .= "<tr>";
        $retval .= "<td><label for=\"id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"id\" value=" . (isset($object->id) ? $object->id : $object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"parentId\">Categoria</label></td>\r\n";
        $retval .= "<td><select name=\"parentId\">\r\n";
        if ((isset($object->id) && $object->id !== 0) || !isset($object->id)) {
            $retval .= $this->getSelectFromList($category_list, isset($object->parentId) ? $object->parentId : 0);
        }
        $retval .= "</select>\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"description\">Descri&ccedil;&atilde;o</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"description\" value=\"" . (isset($object->id) ? $object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"active\">Activa</label></td>\n";
        $retval .= "<td><input type=\"checkbox\" name=\"active\" " . ((isset($object->id) && $object->active) || !isset($object->id) ? "checked" : "") . "></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $category_list, ?int $selected = null): string
    {
        $retval = "";
        /**
         * @var EntryCategory $object
         */
        $object = $this->object;
        if (null === $selected) {
            $selected = $object->id;
        }
        foreach ($category_list as $category) {
            if (($category instanceof EntryCategory)) {
                if ($category->id > 0 && sizeof($category->children) > 0) {
                    $retval .= "<optgroup label=\"{$category->description}\">\r\n";
                    $retval .= "<option value=\"{$category->id}\"" . ($selected == $category->id ? " selected " : "") . ">{$category->description}</option>\n";
                    foreach ($category->children as $child) {
                        $retval .= "<option value=\"{$child->id}\"" . ($selected == $child->id ? " selected " : "") . ">{$child->description}</option>\n";
                    }
                    $retval .= "</optgroup>\r\n";
                } else {
                    if ($category->parentId == 0 || !isset($category->parentId)) {
                        $retval .= "<option value=\"{$category->id}\"" . ($selected == $category->id ? " selected " : "") . ">{$category->description}</option>\n";
                    }
                }
            }
        }
        return $retval;
    }
}
