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

$app = Application::create();
$app->init();
$router = new Router($app);
$request = new HttpRequest();
$session = $app->session();
try {
    $frontend = "index.php?action=";
    $action = strtolower($request->input('action', 'login'));
    if ($action !== 'login' && $session->isExpired()) {
        $session->logout();
        $app->redirector()->to("{$frontend}login&expired=1");
    }
    if (!in_array($action, $router->publicActions(), true) && !$session->isAuthenticated()) {
        $app->redirector()->to("{$frontend}login&needsauth=1");
    }
    if ($action === 'login' && $session->isAuthenticated()) {
        $app->redirector()->to("{$frontend}ledger_entries");
    }
    if ($app->needsUpdate() && $action !== 'update') {
        $app->redirector()->to("{$frontend}update");
    }
    if ($session->isAuthenticated()) {
        $session->refreshExpiration();
    }
    $router->handleRequest($app, $action, $request);
    exit;
} catch (Exception $e) {
    $app->setErrorMessage($e->getMessage());
    $router->handleRequest($app, 'application_error');
}
