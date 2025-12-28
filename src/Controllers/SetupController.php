<?php

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\StorageEngineInterface;
use PHPLedger\Domain\User;
use PHPLedger\Services\ConfigHandler;
use PHPLedger\Storage\StorageManager;
use PHPLedger\Util\SetupState;
use PHPLedger\Views\Templates\SetupViewFormTemplate;
use PHPLedger\Views\Templates\SetupViewTemplate;
use Throwable;

final class SetupController extends AbstractViewController
{
    private const string JSON_HEADER = 'Content-Type: application/json';
    private ConfigHandler $configHandler;
    private StorageManager $storageManager;
    private bool $ajax = false;

    protected function handle(): void
    {
        $this->configHandler = new ConfigHandler($this->app->config());
        $this->storageManager = new StorageManager($this->app);
        $this->ajax = (int)$this->request->input('ajax', 0) === 1;
        $view = new SetupViewTemplate();
        $setupViewFormTemplate = new SetupViewFormTemplate();
        $config = ['title' => ''];
        $messages = [];
        $success = null;

        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels(
                $this->app->l10n(),
                [
                    'admin_password',
                    'admin_username',
                    'application_name',
                    'config_saved',
                    'create_admin_user',
                    'create_db',
                    'database',
                    'db_created',
                    'error_config_setting',
                    'from',
                    'host',
                    'migrations_applied',
                    'mysql_settings',
                    'no_migrations',
                    'password',
                    'pending_migrations',
                    'port',
                    'save_anyway',
                    'save',
                    'setup_complete',
                    'smtp_host',
                    'smtp_port',
                    'storage_type',
                    'test_db',
                    'url',
                    'user',
                ]
            )
        );
        $pendingMigrations = [];
        try {
            $config = $this->configHandler->getCurrent();
            $state  = $this->determineSetupState($config);
            if ($this->request->method() === 'POST') {
                [$config, $success, $messages] = $this->handlePost($config);
                $state = $this->determineSetupState($config);
            }
            if (!empty($config['storage']['settings'] ?? '')) {
                $pendingMigrations = $this->getEngine($config)->pendingMigrations($config['storage']['settings'] ?? []);
            }
        } catch (Throwable $e) {
            $messages = [$e->getMessage()];
            $success = false;
        }

        if (!$this->ajax) {
            $view->render(array_merge($this->uiData, [
                'state' => $state,
                'config' => $config,
                'success' => $success,
                'messages' => $messages,
                'pagetitle' => $this->app->l10n()->l('Configuration & Setup'),
                'lang' => $this->app->l10n()->html(),
                'setupViewFormTemplate' => $setupViewFormTemplate,
                'pending_migrations' => $pendingMigrations
            ]));
        }
    }

    private function handlePost(array $current): array
    {
        if (!$this->validateCsrf()) {
            return $this->fail($current, $this->app->l10n()->l('invalid_csrf'));
        }
        $this->uiData['csrf'] = $this->app->csrf()->inputField();
        $config = $this->buildConfigFromRequest($current);
        $action = $this->request->input('itemaction', '');
        $this->app->logger()->debug("ACTION " . $action, __FUNCTION__);
        return match ($action) {
            'test_storage'   => $this->stepTestStorage($config),
            'create_storage' => $this->stepCreateStorage($config),
            'run_migrations' => $this->stepRunMigrations($config),
            'create_admin'   => $this->stepCreateAdmin($config),
            'save'           => $this->stepSaveConfig($config),
            default          => [$config, null, []],
        };
    }
    private function stepCreateAdmin(array $config): array
    {
        try {
            $this->stepCreateInitialUser($config);
            $message = 'Admin user created';
            $this->respond([
                'success' => true,
                'message' => $message,
            ], $config);
            return [$config, true, [$message]];
        } catch (Throwable $e) {
            return $this->fail($config, $e->getMessage());
        }
    }

    private function stepTestStorage(array $config): array
    {
        try {
            $engine = $this->getEngine($config);
            $result = $engine->test($config['storage']['settings']);
            $this->respond([
                'success' => true,
                'message' => $result['message'],
                'db_exists' => $result['db_exists'],
            ], $config);

            return [$config, true, [$result['message']]];
        } catch (Throwable $e) {
            return $this->fail($config, $e->getMessage());
        }
    }

    private function stepCreateStorage(array $config): array
    {
        try {
            $engine = $this->getEngine($config);
            $engine->create($config['storage']['settings']);
            $this->respond([
                'success' => true,
                'message' => $this->uiData['label']['db_created'],
                'db_exists' => true,
            ], $config);
            return [$config, true, [$this->uiData['label']['db_created']]];
        } catch (Throwable $e) {
            $this->app->logger()->debug("Exception while creating:" . $e->getMessage());
            return $this->fail($config, $e->getMessage());
        }
    }

    private function stepSaveConfig(array $config): array
    {
        if (!$this->app->config()->validate($config)) {
            return $this->fail(
                $config,
                $this->app->l10n()->l(
                    'error_config_setting',
                    $this->app->config()->getValidationMessage()
                )
            );
        }

        $this->configHandler->save($config);

        $messages = [$this->uiData['label']['config_saved']];
        $pendingMigrations = [];

        try {
            $engine = $this->getEngine($config);
            $result = $engine->test($config['storage']['settings']);
            if ($result['db_exists']) {
                $pendingMigrations = $engine->pendingMigrations($config['storage']['settings']);
                if (!empty($pendingMigrations)) {
                    $messages[] = $this->app->l10n()->l('pending_migrations');
                }
            }
        } catch (Throwable $e) {
            $messages[] = $e->getMessage();
        }

        $this->respond([
            'success' => true,
            'message' => implode("\n", $messages),
            'pending_migrations' => $pendingMigrations,
        ], $config);

        return [$config, true, $messages];
    }

    private function stepRunMigrations(array $config): array
    {
        try {
            $engine = $this->getEngine($config);
            $pending = $engine->pendingMigrations($config['storage']['settings']);

            if (empty($pending)) {
                $message = $this->uiData['label']['no_migrations'] ?? 'No migrations to apply';
            } else {
                $engine->runMigrations($config['storage']['settings']);
                $message = $this->uiData['label']['migrations_applied'] ?? 'Migrations applied';
            }
            $this->respond([
                'success' => true,
                'message' => $message,
            ], $config);
            return [$config, true, [$message]];
        } catch (Throwable $e) {
            $this->app->logger()->debug("Exception while running migrations");
            $this->app->logger()->debug($e->getMessage());
            $this->app->logger()->debug($e->getTraceAsString());
            return $this->fail($config, $e->getMessage());
        }
    }

    private function stepCreateInitialUser(array $config): void
    {
        $user = $this->app->dataFactory()::user();
        $user->setPassword($config['admin']['password'] ?? '');
        $user->setProperty('userName', $config['admin']['username'] ?? '');
        $user->setProperty('role', User::USER_ROLE_ADM);
        $user->setProperty('active', 1);
        $user->update();
    }

    private function determineSetupState(array $config): SetupState
    {
        $state = SetupState::CONFIG_REQUIRED;

        try {
            if (empty($config['storage']['type']) || empty($config['storage']['settings'])) {
                return $state;
            }

            $engine = $this->getEngine($config);
            $test = $engine->test($config['storage']['settings']);

            $state = match (true) {
                !$test['db_exists'] => SetupState::STORAGE_MISSING,
                !empty($engine->pendingMigrations($config['storage']['settings'])) => SetupState::MIGRATIONS_PENDING,
                empty($this->getUsers()) => SetupState::ADMIN_MISSING,
                default => SetupState::COMPLETE,
            };
        } catch (Throwable) {
            $state = SetupState::CONFIG_REQUIRED;
        }

        return $state;
    }

    private function getEngine(array $config): StorageEngineInterface
    {
        return $this->storageManager->getEngine($config['storage']['type'] ?? 'mysql');
    }

    private function respond(array $payload, array $config = []): void
    {
        if ($this->ajax) {
            $payload['csrf'] = $this->app->csrf()->getToken();
            $payload['state'] = $this->determineSetupState($config);
            $this->sendJson($payload);
        }
    }

    private function sendJson(array $data): never
    {
        $this->app->headerSender()->send(self::JSON_HEADER);
        echo json_encode($data);
        exit;
    }

    private function fail(array $config, string $message): array
    {
        $this->respond(['success' => false, 'message' => $message], $config);
        return [$config, false, [$message]];
    }

    private function validateCsrf(): bool
    {
        return $this->app->csrf()->validateToken($this->request->input('_csrf_token', ''));
    }

    private function getUsers(): array
    {
        return $this->app->dataFactory()::user()->getList();
    }

    private function buildConfigFromRequest(array $current): array
    {
        $i = fn($k, $d = '') => trim($this->request->input($k, $d));

        return [
            'version' => $current['version'] ?? 2,
            'title' => $i('title', $current['title'] ?? ''),
            'storage' => [
                'type' => $i('storage_type', $current['storage']['type'] ?? 'mysql'),
                'settings' => [
                    'host' => $i('storage_host', $current['storage']['settings']['host'] ?? ''),
                    'database' => $i('storage_database', $current['storage']['settings']['database'] ?? ''),
                    'port' => (int)$i('storage_port', $current['storage']['settings']['port'] ?? 3306),
                    'user' => $i('storage_user', $current['storage']['settings']['user'] ?? ''),
                    'password' => $i('storage_password', $current['storage']['settings']['password'] ?? ''),
                ],
            ],
            'smtp' => [
                'host' => $i('smtp_host', $current['smtp']['host'] ?? ''),
                'port' => (int)$i('smtp_port', $current['smtp']['port'] ?? 25),
                'from' => $i('smtp_from', $current['smtp']['from'] ?? ''),
            ],
            'admin' => [
                'username' => $i('admin_username', $current['admin']['username'] ?? ''),
                'password' => $i('admin_password', $current['admin']['password'] ?? ''),
            ],
            'url' => $i('url', $current['url'] ?? ''),
        ];
    }
}
