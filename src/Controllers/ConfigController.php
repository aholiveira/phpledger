<?php

namespace PHPLedger\Controllers;

use Exception;
use PHPLedger\Domain\User;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\ConfigView;

final class ConfigController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new ConfigView();
        $success = false;
        $data = ["title" => ""];
        $hasPermission = false;
        $messages = [];
        try {
            $this->checkUserPermission();
            $hasPermission = true;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                [$data, $success, $messages] = $this->processPost();
            } else {
                $data = Config::getCurrent();
            }
        } catch (Exception $e) {
            $messages = [$e->getMessage()];
        }
        $view->render($data, $hasPermission, $success, $messages);
    }

    private function checkUserPermission(): void
    {
        $username = $this->app->session()->get('user', '');
        $user = !empty($username) ? ObjectFactory::user()::getByUsername($username) : null;
        if (!($user instanceof User)) {
            Redirector::to("index.php?action=login");
            throw new PHPLedgerException('You must be logged in to view this page.');
        }
        if (!$user->hasRole(User::USER_ROLE_ADM)) {
            throw new PHPLedgerException("You do not have permission to view this page.");
        }
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
