<?php

/**
 * Configuration class - Handles setting and getting configuration values
 *
 * @author Antonio Henrique Oliveira <aholiveira@gmail.com>
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 * 
 */
class config
{
    private $parameters = array();

    public function __construct()
    {
    }

    public function setParameterValue($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    public function getParameter($parameter)
    {
        if (array_key_exists($parameter, $this->parameters)) {
            return $this->parameters[$parameter];
        } else {
            return null;
        }
    }
}
