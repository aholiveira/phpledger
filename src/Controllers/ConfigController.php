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
        $view = new ConfigView();
        $success = false;

        [$userOk, $hasPermission, $data, $messages] = $this->checkUserPermission();

        if ($userOk && $hasPermission) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                [$data, $success, $messages] = $this->processPost();
            } else {
                $messages = [];
                $data = Config::getCurrent();
            }
        }
        $view->render($data, $hasPermission, $success, $messages);
    }

    private function checkUserPermission(): array
    {
        $data = ["title" => ""];
        $messages = [];
        $hasPermission = false;
        $userOk = false;
        $user = !empty($_SESSION['user']) ? ObjectFactory::user()::getByUsername($_SESSION['user']) : null;
        if ($user instanceof User) {
            $userOk = true;
            if ($user->hasRole(User::USER_ROLE_ADM)) {
                $hasPermission = true;
            } else {
                $messages = ['You do not have permission to view this page.'];
            }
        } else {
            $messages = ['You must be logged in to view this page.'];
        }
        return [$userOk, $hasPermission, $data, $messages];
    }
    private function processPost(): array
    {
        $data = Config::getCurrent();
        $success = false;
        $messages = [];

        if (!CSRF::validateToken($_POST['_csrf_token'] ?? '')) {
            $messages = ['Invalid CSRF token.'];
            return [$data, false, $messages];
        }

        $new = [
            'version' => $data['version'] ?? 2,
            'title' => trim($_POST['title'] ?? ''),
            'storage' => [
                'type' => $_POST['storage_type'] ?? '',
                'settings' => [
                    'host' => trim($_POST['storage_host'] ?? ''),
                    'database' => trim($_POST['storage_database'] ?? ''),
                    'port' => (int)trim($_POST['storage_port'] ?? ''),
                    'user' => trim($_POST['storage_user'] ?? ''),
                    'password' => trim($_POST['storage_password'] ?? '')
                ]
            ],
            'smtp' => [
                'host' => trim($_POST['smtp_host'] ?? ''),
                'port' => (int)trim($_POST['smtp_port'] ?? ''),
                'from' => trim($_POST['smtp_from'] ?? '')
            ],
            'admin' => [
                'username' => $data['admin']['username'] ?? 'admin',
                'password' => $data['admin']['password'] ?? 'admin'
            ],
            'url' => trim($_POST['url'] ?? '')
        ];

        if (!Config::validate($new)) {
            $messages = ['Some configuration values are invalid.', Config::getValidationMessage()];
            return [$new, false, $messages];
        }

        Config::set('title', $new['title']);
        Config::set('storage.type', $new['storage']['type']);
        Config::set('storage.settings.host', $new['storage']['settings']['host']);
        Config::set('storage.settings.database', $new['storage']['settings']['database']);
        Config::set('storage.settings.user', $new['storage']['settings']['user']);
        Config::set('storage.settings.password', $new['storage']['settings']['password']);
        Config::set('storage.settings.port', (int)$new['storage']['settings']['port']);
        Config::set('smtp.host', $new['smtp']['host']);
        Config::set('smtp.port', (int)$new['smtp']['port']);
        Config::set('smtp.from', $new['smtp']['from']);
        Config::set('url', $new['url']);

        try {
            Config::save();
            $success = true;
            $messages = ['Configuration saved successfully.'];
        } catch (\Exception $e) {
            $messages = ['Unable to save configuration: ' . $e->getMessage()];
        }
        return [$new, $success, $messages];
    }
}
