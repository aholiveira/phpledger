<?php

namespace PHPLedger\Controllers;

use Exception;
use PHPLedger\Domain\User;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\Templates\ConfigViewTemplate;

final class ConfigController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new ConfigViewTemplate();
        $messages = [];
        $success = false;
        $config = ["title" => ""];
        $hasPermission = false;

        try {
            $this->checkUserPermission();
            $hasPermission = true;

            if ($this->request->method() === 'POST') {
                [$config, $success, $messages] = $this->processPost();
            } else {
                $config = Config::getCurrent();
            }
        } catch (Exception $e) {
            $messages = [$e->getMessage()];
        }
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels(
                $this->app->l10n(),
                [
                    "application_name",
                    "smtp_host",
                    "smtp_port",
                    "from",
                    "url",
                    "storage_type",
                    "mysql_settings",
                    "save",
                ]
            )
        );
        $view->render(array_merge($this->uiData, [
            'config' => $config,
            'hasPermission' => $hasPermission,
            'success' => $success,
            'messages' => $messages,
            'pagetitle' => $this->app->l10n()->l("Configuration"),
            'lang' => $this->app->l10n()->html(),
        ]));
    }

    private function checkUserPermission(): void
    {
        $username = $this->app->session()->get('user', '');
        $user = $username ? ObjectFactory::user()::getByUsername($username) : null;

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
        $messages = [];
        $success = false;

        if (!CSRF::validateToken($this->request->input('_csrf_token', ''))) {
            $messages[] = 'Invalid CSRF token.';
            return [$data, false, $messages];
        }

        $new = [
            'version' => $data['version'] ?? 2,
            'title' => trim($this->request->input('title', '')),
            'storage' => [
                'type' => $this->request->input('storage_type', ''),
                'settings' => [
                    'host' => trim($this->request->input('storage_host', '')),
                    'database' => trim($this->request->input('storage_database', '')),
                    'port' => (int)$this->request->input('storage_port', 3306),
                    'user' => trim($this->request->input('storage_user', '')),
                    'password' => trim($this->request->input('storage_password', '')),
                ]
            ],
            'smtp' => [
                'host' => trim($this->request->input('smtp_host', '')),
                'port' => (int)$this->request->input('smtp_port', 25),
                'from' => trim($this->request->input('smtp_from', '')),
            ],
            'admin' => $data['admin'] ?? ['username' => 'admin', 'password' => 'admin'],
            'url' => trim($this->request->input('url', '')),
        ];

        if (!Config::validate($new)) {
            return [$new, false, ['Some configuration values are invalid.', Config::getValidationMessage()]];
        }

        foreach ($new['storage']['settings'] as $k => $v) {
            Config::set("storage.settings.$k", $v);
        }
        Config::set('title', $new['title']);
        Config::set('storage.type', $new['storage']['type']);
        Config::set('smtp.host', $new['smtp']['host']);
        Config::set('smtp.port', $new['smtp']['port']);
        Config::set('smtp.from', $new['smtp']['from']);
        Config::set('url', $new['url']);

        try {
            Config::save();
            $success = true;
            $messages = ['Configuration saved successfully.'];
        } catch (Exception $e) {
            $messages = ['Unable to save configuration: ' . $e->getMessage()];
        }

        return [$new, $success, $messages];
    }
}
