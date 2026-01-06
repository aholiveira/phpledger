<?php

/**
 * Main entry point of the application
 * This does basic defines and checks if PHP version is supported
 * It routes requests to the appropriate controllers
 *
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

declare(strict_types=1);

if (PHP_VERSION_ID < 80400) {
    die('PHP >= 8.4.0 required');
}

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__));
}

require_once ROOT_DIR . '/vendor/autoload.php';

use PHPLedger\ApplicationFactory;
use PHPLedger\Exceptions\ApplicationNotInstalledException;
use PHPLedger\Exceptions\InvalidCsrfTokenException;
use PHPLedger\Http\HttpRequest;
use PHPLedger\Routing\Router;

$frontend = "index.php?action=";
$request = new HttpRequest();
$action = strtolower($request->input('action', 'login'));
$setupActions = ['setup'];
$setup = in_array($action, $setupActions, true);
$app = ApplicationFactory::create();
$router = new Router($app);
$session = $app->session();

// Global error handling
set_exception_handler(fn(Throwable $t) => handleError($app, $router, $t));
register_shutdown_function(fn() => handleShutdown($app, $router));
try {
    $app->init($setup);
    if (!$setup && $app->needsSetup()) {
        $app->logger()->info("Application not installed. Going to run setup.");
        $app->redirector()->to("{$frontend}setup");
    }
} catch (ApplicationNotInstalledException $e) {
    $app->logger()->info("Application not installed. Going to run setup.");
    if (!$setup) {
        $app->redirector()->to("{$frontend}setup");
        exit;
    }
}

try {
    $app->init($setup);

    if (!$setup && $app->needsSetup()) {
        $app->logger()->info("Application requires setup. Redirecting.");
        $app->redirector()->to("{$frontend}setup");
    }
    handleSessionRedirects($app, $router, $session, $frontend, $action, $setup);
    $router->handleRequest($app, $action, $request);
} catch (InvalidCsrfTokenException) {
    $app->redirector()->to("{$frontend}login");
} catch (Throwable $e) {
    $app->logger()->debug($e->getTraceAsString());
    $app->setErrorMessage($e->getMessage());
    $router->handleRequest($app, 'application_error');
}

function handleError($app, $router, Throwable $t): void
{
    $app->setErrorMessage($t->getMessage());
    $router->handleRequest($app, 'application_error');
}

function handleShutdown($app, $router): void
{
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $app->setErrorMessage("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
        $router->handleRequest($app, 'application_error');
    }
}

function handleSessionRedirects($app, $router, $session, $frontend, $action, $setup): void
{
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

    if (!$setup && $app->needsSetup()) {
        $app->redirector()->to("{$frontend}setup");
    }

    if ($session->isAuthenticated()) {
        $session->refreshExpiration();
    }
}
