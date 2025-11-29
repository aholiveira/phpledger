<?php

/**
 * Prepended file on each call to a PHP file
 * This does basic defines and checks if PHP version is supported
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 * @since 0.2.0
 *
 */

if (PHP_VERSION_ID < 70000) {
    die('PHP >= 7.0.0 required');
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPLedger\Util\Logger;
use PHPLedger\Application;

const BACKEND = "mysql";
const VERSION = "0.4.506";
const ROOT_DIR = __DIR__;

new Logger(ROOT_DIR . "/logs/ledger.log");
Application::init();
function normalizeNumber(?float $number): string
{
    return null === $number ? "" : number_format($number, 2);
}
