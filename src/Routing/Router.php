<?php
namespace PHPLedger\Routing;

use PHPLedger\Controllers\LoginController;
use PHPLedger\Controllers\ConfigController;

final class Router
{
    private array $legacyPages = [
        'ledger_entries' => 'ledger_entries.php',
        'balances'      => 'balances.php',
        'accounts'      => 'accounts.php',
        'account_types' => 'account_types_list.php',
        'entry_types'   => 'entry_types_list.php',
        'report_month'  => 'report_month.php',
        'report_year'   => 'report_year.php'
    ];

    private array $migratedActions = [
        'login'  => LoginController::class,
        'config' => ConfigController::class
    ];

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

        // fallback to login page
        header('Location: index.php?action=login');
        exit;
    }
}
