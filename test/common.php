<?php
/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
use PHPLedger\Util\Config;

if (!defined("DEBUG")) {
    define("DEBUG", true);
}

require_once __DIR__ . "/prepend.php";
Config::init(__DIR__ . '/config.json');
