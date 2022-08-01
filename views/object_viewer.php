<?php
/**
 * Generic class for an object viewer
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
abstract class object_viewer
{
    protected iobject $_object;
    public function __construct(iobject $object)
    {
        $this->_object = $object;
    }
    public function __toString()
    {
        return get_called_class();
    }
    public function setObject(iobject $object)
    {
        $this->_object = $object;
    }
    abstract public function printObject(): string;
    abstract public function printObjectList(array $object_list): string;
}
