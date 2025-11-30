<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Views\ConfigView;

class ConfigController
{
    public function handle(): void
    {
        $user = $_SESSION['user'] ? ObjectFactory::user()::getByUsername($_SESSION['user']) : null;
        $view = new ConfigView();
        $hasPermission = false;

        if (!$user instanceof User) {
            // Not logged in, render page with message but keep menu
            $view->render(["title" => ""], $hasPermission, false, ['You must be logged in to view this page.']);
            return;
        }
        if (!($user instanceof User) || !$user->hasRole(User::USER_ROLE_ADM)) {
            // Render page with message but keep menu
            $hasPermission = false;
            $view->render(["title" => ""], $hasPermission, false, ['You do not have permission to view this page.']);
            return;
        }
        $hasPermission = true;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['_csrf_token'] ?? '')) {
                $view->render(Config::getCurrent(), $hasPermission, false, ['Invalid CSRF token.']);
                return;
            }

            $new = [
                'version' => Config::getCurrent()['version'] ?? 2,
                'title' => trim($_POST['title'] ?? ''),
                'storage' => [
                    'type' => $_POST['storage_type'] ?? '',
                    'settings' => [
                        'host' => trim($_POST['storage_host'] ?? ''),
                        'database' => trim($_POST['storage_database'] ?? ''),
                        'user' => trim($_POST['storage_user'] ?? ''),
                        'password' => trim($_POST['storage_password'] ?? '')
                    ]
                ],
                'smtp' => [
                    'host' => trim($_POST['smtp_host'] ?? ''),
                    'port' => trim($_POST['smtp_port'] ?? ''),
                    'from' => trim($_POST['smtp_from'] ?? '')
                ],
                'admin' => [
                    'username' => Config::getCurrent()['admin']['username'] ?? 'admin',
                    'password' => Config::getCurrent()['admin']['password'] ?? 'admin'
                ],
                'url' => trim($_POST['url'] ?? '')
            ];

            if (!Config::validate($new)) {
                $view->render($new, $hasPermission, false, ['Some configuration values are invalid.', Config::getValidationMessage()]);
                return;
            }

            Config::set('title', trim($_POST['title'] ?? ''));
            Config::set('storage.type', $_POST['storage_type'] ?? '');
            Config::set('storage.settings.host', trim($_POST['storage_host'] ?? ''));
            Config::set('storage.settings.database', trim($_POST['storage_database'] ?? ''));
            Config::set('storage.settings.user', trim($_POST['storage_user'] ?? ''));
            Config::set('storage.settings.password', trim($_POST['storage_password'] ?? ''));
            Config::set('storage.settings.port', (int)trim($_POST['storage_port'] ?? ''));
            Config::set('smtp.host', trim($_POST['smtp_host'] ?? ''));
            Config::set('smtp.port', (int)trim($_POST['smtp_port'] ?? ''));
            Config::set('smtp.from', trim($_POST['smtp_from'] ?? ''));
            Config::set('url', trim($_POST['url'] ?? ''));

            try {
                Config::save();
                $view->render($new, $hasPermission, true, ['Configuration saved successfully.']);
                return;
            } catch (\Exception $e) {
                $view->render($new, $hasPermission, false, ['Unable to save configuration: ' . $e->getMessage()]);
                return;
            }
        }
        $view->render(Config::getCurrent(), $hasPermission, false, []);
    }
}
