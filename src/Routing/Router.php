<?php

namespace PHPLedger\Routing;

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
use PHPLedger\Controllers\ReportMonthController;
use PHPLedger\Controllers\ReportYearController;
use PHPLedger\Controllers\ResetPasswordController;
use PHPLedger\Controllers\UpdateStorageController;

final class Router
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
        'report_month'      => ReportMonthController::class,
        'report_year'       => ReportYearController::class,
        'resetpassword'     => ResetPasswordController::class,
        'update'            => UpdateStorageController::class,
    ];

    /**
     * Handles the incoming request based on the action parameter.
     * @param string $action The action to handle.
     * @return void
     * @throws \Exception
     */

    public function handleRequest(string $action): void
    {
        if (isset($this->actionMap[$action])) {
            $controllerClass = $this->actionMap[$action];
            $controller = new $controllerClass();
            $controller->handle();
            return;
        }
        header('Location: index.php?action=login');
    }

    /** Returns a whitelist of all valid actions */
    public static function getAllowedActions(): array
    {
        return array_keys((new self())->actionMap);
    }
}
