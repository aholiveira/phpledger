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
        $this->uiData['label'] = $this->buildBaseLabels($app->l10n());
        $this->prepareUi();
        $this->handle();
    }

    private function buildBaseLabels(L10nServiceInterface $l10n): array
    {
        $base = [
            'ledger_entries',
            'balances',
            'accounts',
            'account_types',
            'entry_types',
            'report',
            'configuration',
            'config',
            'logout',
            'version',
            'yes',
            'no',
            'add',
            'edit',
            'actions',
            'active',
            'open',
            'close',
            'id',
            'my_profile',
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
            $user = $this->app->dataFactory()->user()::getByUsername($this->app->session()->get('user', ''));
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
