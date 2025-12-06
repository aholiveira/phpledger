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

if (PHP_VERSION_ID < 80400) {
    die('PHP >= 8.4.0 required');
}

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__));
}

require_once ROOT_DIR . '/vendor/autoload.php';

use PHPLedger\Application;
use PHPLedger\Http\HttpRequest;
use PHPLedger\Routing\Router;
use PHPLedger\Util\Redirector;
use PHPLedger\Util\SessionManager;

$app = new Application();
$router = new Router($app);
$request = new HttpRequest();
$session = new SessionManager($app);
try {
    $action = strtolower($_GET['action'] ?? 'login');
    if (!in_array($action, $router->publicActions(), true) && $session->isExpired()) {
        Redirector::to("index.php");
    }
    $router->handleRequest($app, $action, $request);
} catch (Exception $e) {
    Application::setErrorMessage($e->getMessage());
    $router->handleRequest($app, 'application_error');
}
