<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
if (!defined("BACKEND") || !defined("OBJECTS_DIR")) {
    die("This file should only be included!");
}

include_once OBJECTS_DIR . "/interfaces/idata_storage.php";
include_once OBJECTS_DIR . "/interfaces/ireport.php";
include_once OBJECTS_DIR . "/interfaces/iobject.php";
include_once OBJECTS_DIR . "/interfaces/iobject_factory.php";

if (defined("BACKEND") && file_exists(OBJECTS_DIR . "/" . BACKEND . "/" . basename(__FILE__))) {
    include_once OBJECTS_DIR . "/" . BACKEND . "/" . basename(__FILE__);
}
