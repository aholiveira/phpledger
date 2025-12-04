<?php

/**
 * Main entry point of the application
 * This does basic defines and checks if PHP version is supported
 * It routes requests to the appropriate controllers
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 */

declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    die('PHP >= 8.0.0 required');
}

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__);
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPLedger\Application;
use PHPLedger\Routing\Router;

$router = new Router();
try {
    Application::init();
    $router->handleRequest($_GET['action'] ?? 'login');
} catch (Exception $e) {
    Application::setErrorMessage($e->getMessage());
    $router->handleRequest('application_error');
}
