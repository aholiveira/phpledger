<?php

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Contracts\RequestInterface;
use PHPLedger\Contracts\ViewControllerInterface;
use PHPLedger\Domain\User;
use PHPLedger\Util\UiBuilder;
use PHPLedger\Version;

abstract class AbstractViewController implements ViewControllerInterface
{
    protected RequestInterface $request;
    protected ApplicationObjectInterface $app;
    abstract protected function handle(): void;
    protected array $uiData = ['label' => []];

    public function handleRequest(ApplicationObjectInterface $app, RequestInterface $request): void
    {
        $this->request = $request;
        $this->app = $app;
        $this->uiData['label'] = $this->buildLabels($app->l10n());
        $this->prepareUi();
        $this->handle();
    }

    private function buildLabels(L10nServiceInterface $l10n): array
    {
        $base = [
            'account_entries',
            'account_types',
            'account',
            'accounts',
            'actions',
            'active',
            'add',
            'admin_account_help',
            'admin_account',
            'admin_password',
            'admin_username',
            'amount',
            'application_name',
            'apply_migrations',
            'are_you_sure_you_want_to_delete',
            'average',
            'back_to_balances',
            'back_to_list',
            'balance',
            'balances',
            'basic_configuration_help',
            'basic_configuration',
            'calculate',
            'category',
            'check_your_data',
            'clear_filter',
            'close',
            'closeDate',
            'config_saved',
            'config',
            'configuration',
            'create_admin_user',
            'create_db',
            'create_storage',
            'currency',
            'database',
            'date',
            'db_created',
            'dc',
            'delete',
            'deposit',
            'deposits',
            'description',
            'display_name',
            'download_data',
            'download_raw_csv',
            'download_raw_data',
            'download_report_csv',
            'edit_account',
            'edit_category',
            'edit',
            'email_settings_help',
            'email_settings',
            'email',
            'end',
            'entries',
            'entry_types',
            'error_config_setting',
            'expense',
            'filter',
            'first_name',
            'from',
            'full_name',
            'host',
            'iban',
            'id',
            'income',
            'invalid_csrf',
            'last_name',
            'ledger_entries',
            'list',
            'login_page',
            'login',
            'logout',
            'migrations_applied',
            'my_profile',
            'mysql_settings_help',
            'mysql_settings',
            'name_required',
            'name',
            'no_admin_user_detected',
            'no_filter',
            'no_migrations',
            'no',
            'number',
            'open',
            'openDate',
            'password_recovery',
            'password',
            'pending_db_migrations_detected',
            'pending_migrations',
            'percent',
            'period',
            'port',
            'previous_balance',
            'remarks',
            'report',
            'save_anyway',
            'save',
            'savings',
            'send_reset_link',
            'setup_complete',
            'smtp_host_help',
            'smtp_host',
            'smtp_port',
            'start',
            'storage_does_not_exist',
            'storage_settings_help',
            'storage_settings',
            'storage_type',
            'swift',
            'test_db',
            'total',
            'totals',
            'type',
            'url',
            'user',
            'username',
            'verify_password',
            'version',
            'withdraw',
            'withdrawals',
            'yes',
        ];
        return $this->buildL10nLabels($l10n, $base);
    }

    protected function buildL10nLabels(L10nServiceInterface $l10n, array $keys): array
    {
        $labels = [];
        foreach ($keys as $key) {
            $labels[$key] = $l10n->l($key);
        }
        return $labels;
    }

    protected function prepareUi(): void
    {
        $app = $this->app;
        $l10n = $app->l10n();
        $lang = $l10n->lang();
        $session = $app->session();
        $expires = date("Y-m-d H:i:s", $session->get('expires', 0));
        if ($this->app->session()->isAuthenticated()) {
            $isAdmin = $session->get('isAdmin', false);
            $user = $this->app->dataFactory()->user()->getByUsername($this->app->session()->get('user', ''));
            if ($user instanceof User) {
                $this->uiData['label']['hello'] = $l10n->l('hello', $user->getProperty('firstName', ''));
            }
        } else {
            $isAdmin = false;
        }
        $menuActions = [
            'ledger_entries',
            'balances',
            'accounts',
            'account_types',
            'entry_types',
            'report',
        ];
        $menuActions[] = 'my_profile';
        $menuActions[] = 'logout';
        foreach ($menuActions as $a) {
            $menuLinks[$a] = 'index.php?' . http_build_query([
                'action' => $a,
                'lang'   => $lang
            ]);
        }
        $footer = [
            'repo' => 'https://github.com/aholiveira/phpledger',
            'versionText' => $l10n->l("version", Version::string()),
            'sessionExpires' => $l10n->l("session_expires", $expires),
            'languageSelectorHtml' => $this->buildLanguageSelectorHtml($lang),
        ];
        $this->uiData = [
            'label' => $this->uiData['label'],
            'appTitle' => $this->app->config()->get('title', ''),
            'menu' => $menuLinks,
            'footer' => $footer,
            'ui' => new UiBuilder(),
            'isAdmin' => $isAdmin,
            'lang' => $lang,
            'htmlLang' => $l10n->html(),
            'csrf' => $this->app->csrf()->inputField(),
            'action' => $this->request->input('action'),
        ];
    }

    protected function buildLanguageSelectorHtml(string $current, array $requestParams = []): string
    {
        $params = empty($requestParams) ? $this->request->all() : $requestParams;
        unset($params['lang']);
        $other = $current === 'pt-pt' ? 'en-us' : 'pt-pt';
        $params['lang'] = $other;
        $url = 'index.php?' . http_build_query($params);
        $l10n = $this->app->l10n();
        $labels = [
            'pt_selected' => $l10n->l('pt_selected'),
            'en_selected' => $l10n->l('en_selected'),
            'pt_select'   => $l10n->l('pt_select'),
            'en_select'   => $l10n->l('en_select'),
        ];

        $languages = [
            'EN' => $other === 'pt-pt'
                ? '<span aria-label="' . $labels['en_selected'] . '">EN</span>'
                : '<a aria-label="' . $labels['en_select'] . '" href="' . $url . '">EN</a>',
            'PT' => $other === 'pt-pt'
                ? '<a aria-label="' . $labels['pt_select'] . '" href="' . $url . '">PT</a>'
                : '<span aria-label="' . $labels['pt_selected'] . '">PT</span>',
        ];

        return $languages['EN'] . ' | ' . $languages['PT'];
    }
}
