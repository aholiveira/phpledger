<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
if (!defined("DEBUG")) {
    define("DEBUG", true);
}
require_once __DIR__ . "/prepend.php";
config::init(__DIR__ . '/config.json');
$object_factory = new object_factory();
$view_factory = new view_factory();
