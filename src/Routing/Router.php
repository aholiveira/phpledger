<?php

namespace PHPLedger\Routing;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\RequestInterface;
use PHPLedger\Contracts\ViewControllerInterface;
use PHPLedger\Controllers\AccountController;
use PHPLedger\Controllers\AccountsController;
use PHPLedger\Controllers\AccountTypeFormController;
use PHPLedger\Controllers\AccountTypeListController;
use PHPLedger\Controllers\ApplicationErrorController;
use PHPLedger\Controllers\BalancesController;
use PHPLedger\Controllers\ConfigController;
use PHPLedger\Controllers\EntryCategoryFormController;
use PHPLedger\Controllers\EntryCategoryListController;
use PHPLedger\Controllers\ForgotPasswordController;
use PHPLedger\Controllers\LedgerEntriesController;
use PHPLedger\Controllers\LoginController;
use PHPLedger\Controllers\ReportController;
use PHPLedger\Controllers\ResetPasswordController;
use PHPLedger\Controllers\UpdateStorageController;
use PHPLedger\Http\HttpRequest;

class Router
{
    private array $actionMap = [
        'account_type'      => AccountTypeFormController::class,
        'account_types'     => AccountTypeListController::class,
        'account'           => AccountController::class,
        'accounts'          => AccountsController::class,
        'application_error' => ApplicationErrorController::class,
        'balances'          => BalancesController::class,
        'config'            => ConfigController::class,
        'entry_type'        => EntryCategoryFormController::class,
        'entry_types'       => EntryCategoryListController::class,
        'forgotpassword'    => ForgotPasswordController::class,
        'ledger_entries'    => LedgerEntriesController::class,
        'login'             => LoginController::class,
        'logout'            => LoginController::class,
        'report'            => ReportController::class,
        'resetpassword'     => ResetPasswordController::class,
        'update'            => UpdateStorageController::class,
    ];

    public function handleRequest(ApplicationObjectInterface $app, string $action, ?RequestInterface $request = null): void
    {
        $request ??= new HttpRequest();
        if (isset($this->actionMap[$action])) {
            $controllerClass = $this->actionMap[$action];
            $controller = new $controllerClass();
            if ($controller instanceof ViewControllerInterface) {
                $controller->handleRequest($app, $request);
            }
            return;
        }
        header('Location: index.php?action=login');
        exit;
    }

    public function publicActions(): array
    {
        return ['login', 'update', 'resetpassword', 'forgotpassword', 'applicationerror'];
    }
    /** Returns a whitelist of all valid actions */
    public static function getAllowedActions(): array
    {
        return array_keys((new self())->actionMap);
    }
}
