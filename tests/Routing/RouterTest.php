<?php

namespace PHPLedger\Routing;

use PHPLedger\Application;
use PHPLedger\Routing\Router;

// --- dummy controller ---
class DummyController
{
    public static bool $called = false;
    public function handle()
    {
        self::$called = true;
    }
}
global $headers;
function header($str)
{
    global $headers;
    $headers[] = $str;
}

// helper to inject a custom action map
function setActionMap(Router $router, array $map): void
{
    $ref = new \ReflectionClass($router);
    $prop = $ref->getProperty('actionMap');
    $prop->setValue($router, $map);
}

beforeEach(function () {
    DummyController::$called = false;
});
/*
it('router calls handle on valid action', function () {
    $router = new Router();
    $app = new Application();
    setActionMap($router, ['dummy' => DummyController::class]);

    $router->handleRequest($app, 'dummy');

    expect(DummyController::$called)->toBeTrue();
});
/*
it('router outputs redirect string on invalid action in test mode', function () {
    global $headers;
    $router = new Router();
    $app = new Application();
    setActionMap($router, ['dummy' => DummyController::class]);

    $router->handleRequest($app, 'invalid_action');
    expect($headers)->toContain('Location: index.php?action=login');
});

it('getAllowedActions returns all keys', function () {
    $actions = Router::getAllowedActions();
    expect($actions)->toContain('ledger_entries');
    expect($actions)->toContain('account');
    expect($actions)->toContain('login');
    expect(count($actions))->toBe(16);
});
*/
