<?php

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
class entry_category_view extends ObjectViewer
{
    protected EntryCategory $object;
    public function __construct(EntryCategory $object)
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
        if (!($_object instanceof EntryCategory)) {
            return $retval;
        }
        $retval .= "<td class='id' data-label='ID'><a href=\"entry_type.php?tipo_id={$_object->id}\" title=\"Editar a categoria\">{$_object->id}</a></td>";
        $retval .= "<td class='category' data-label='Categoria'>" . (null === $_object->parent_id || $_object->parent_id == 0 ? "" : ($_object->parent_description ?? "")) . "</td>";
        $retval .= "<td class='description' data-label='Descri&ccedil;&atilde;o'>{$_object->description}</td>";
        $retval .= "<td class='amount' data-label='Valor'>" . normalize_number(abs($_object->getBalance())) . "</td>";
        $retval .= "<td class='active checkbox' data-label='Activa'><input title=\"S&oacute; pode alterar o estado em modo de edi&ccedil;&atilde;o\" type=\"checkbox\" onclick=\"return false;\" name=active{$_object->id} " . ($_object->active ? "checked" : "") . "></td>\r\n";
        return $retval;
    }
    public function printObjectList(array $object_list): string
    {
        $retval = "<table class=\"lista entry_category\">\r\n";
        $retval .= "<thead><tr><th>ID</th><th>Categoria</th><th>Descri&ccedil;&atilde;o</th><th>Valor</th><th>Activa</th></tr></thead>\r\n";
        $retval .= "<tbody>\r\n";
        $view = new entry_category_view(ObjectFactory::entryCategory());
        foreach ($object_list as $object) {
            if ($object instanceof EntryCategory) {
                if ($object->parent_id === 0) {
                    $view->setObject($object);
                    $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
                    foreach ($object->children as $child) {
                        $view->setObject($child);
                        $retval .= "<tr>" . $view->printObject() . "</tr>\r\n";
                    }
                }
                if (!isset($object->parent_id)) {
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
        $_object = $this->_object;
        if (!$_object instanceof EntryCategory) {
            return $retval;
        }
        if (isset($_object->id)) {
            $filter = [
                'active' => ['operator' => '=', 'value' => '1'],
                'tipo_id' => ['operator' => '<>', 'value' => "{$_object->id}"]
            ];
        } else {
            $filter = ['active' => ['operator' => '=', 'value' => '1']];
        }
        $category_list = $_object->getList($filter);
        if (isset($_object->id)) {
            foreach ($category_list as $key => $category) {
                if ($category->parent_id == $_object->id || $category->id == $_object->id) {
                    unset($category_list[$key]);
                }
            }
        }
        $retval .= "<tr>";
        $retval .= "<td><label for=\"tipo_id\">ID</label></td>\r\n";
        $retval .= "<td><input type=text readonly size=4 name=\"tipo_id\" value=" . (isset($_object->id) ? $_object->id : $_object->getNextId()) . "></td>\r\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"parent_id\">Categoria</label></td>\r\n";
        $retval .= "<td><select name=\"parent_id\">\r\n";
        if ((isset($_object->id) && $_object->id !== 0) || !isset($_object->id)) {
            $retval .= $this->getSelectFromList($category_list, isset($_object->parent_id) ? $_object->parent_id : 0);
        }
        $retval .= "</select>\n";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"tipo_desc\">Descri&ccedil;&atilde;o</label></td>\n";
        $retval .= "<td><input type=text size=30 maxlength=30 name=\"tipo_desc\" value=\"" . (isset($_object->id) ? $_object->description : "") . "\"></td>";
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td><label for=\"active\">Activa</label></td>\n";
        $retval .= "<td><input type=\"checkbox\" name=\"active\" " . ((isset($_object->id) && $_object->active) || !isset($_object->id) ? "checked" : "") . "></td>";
        $retval .= "</tr>\r\n";
        return $retval;
    }
    public function getSelectFromList(array $category_list, ?int $selected = null): string
    {
        $retval = "";
        /**
         * @var EntryCategory $_object
         */
        $_object = $this->_object;
        if (null === $selected) {
            $selected = $_object->id;
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
                    if ($category->parent_id == 0 || !isset($category->parent_id)) {
                        $retval .= "<option value=\"{$category->id}\"" . ($selected == $category->id ? " selected " : "") . ">{$category->description}</option>\n";
                    }
                }
            }
        }
        return $retval;
    }
}
