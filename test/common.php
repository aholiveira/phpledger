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
include __DIR__ . "/prepend.php";
$config = new config();
include __DIR__ . '/config.php';
$object_factory = new object_factory($config);
$view_factory = new view_factory();
