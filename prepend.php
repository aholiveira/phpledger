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

if (PHP_VERSION_ID < 80000) {
    die('PHP >= 8.0.0 required');
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPLedger\Application;

const ROOT_DIR = __DIR__;

Application::init();
