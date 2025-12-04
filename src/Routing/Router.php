<?php

namespace PHPLedger\Routing;

use PHPLedger\Controllers\AccountController;
use PHPLedger\Controllers\AccountsController;
use PHPLedger\Controllers\AccountTypeListController;
use PHPLedger\Controllers\ApplicationErrorController;
use PHPLedger\Controllers\LoginController;
use PHPLedger\Controllers\ConfigController;
use PHPLedger\Controllers\LedgerEntriesController;
use PHPLedger\Controllers\ResetPasswordController;

final class Router
{
    private array $legacyPages = [
        'ledger_entries' => 'ledger_entries.php',
        'balances'       => 'balances.php',
        'entry_types'    => 'entry_types_list.php',
        'report_month'   => 'report_month.php',
        'report_year'    => 'report_year.php'
    ];

    private array $migratedActions = [
        'login'         => LoginController::class,
        'config'        => ConfigController::class,
        'account_types' => AccountTypeListController::class,
        'accounts'      => AccountsController::class,
        'account'       => AccountController::class,
        'application_error' => ApplicationErrorController::class,
        'resetpassword' => ResetPasswordController::class
    ];

    /**
     * Handles the incoming request based on the action parameter.
     * @param string $action The action to handle.
     * @return void
     * @throws \Exception
     */

    public function handleRequest(string $action): void
    {
        if (isset($this->migratedActions[$action])) {
            $controllerClass = $this->migratedActions[$action];
            $controller = new $controllerClass();
            $controller->handle();
            return;
        }

        if (isset($this->legacyPages[$action])) {
            $file = $this->legacyPages[$action];
            if (file_exists($file)) {
                require $file;
                return;
            }
        }

        header('Location: index.php?action=login');
        exit;
    }

    /** Returns a whitelist of all valid actions */
    public static function getAllowedActions(): array
    {
        return array_merge(
            array_keys((new self())->legacyPages),
            array_keys((new self())->migratedActions)
        );
    }
}
