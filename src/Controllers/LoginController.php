<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Util\Config;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Logger;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\Templates\LoginViewTemplate;

final class LoginController extends AbstractViewController
{
    private string $postUser = '';
    private bool $userAuth = false;
    public function handle(): void
    {
        if ($this->request->method() === 'GET' && $this->request->input('action', '') === 'logout') {
            $this->logout();
            return;
        }
        if ($this->request->method() === 'POST') {
            $this->login();
        }
        $this->renderView();
    }

    private function logout(): void
    {
        $user = $this->app->session()->get('user', '');
        if (!empty($user)) {
            $defaults = $this->app->dataFactory()::defaults()::getByUsername($user);
            if ($defaults !== null) {
                $defaults->lastVisitedUri = '';
                $defaults->lastVisitedAt = time();
                $defaults->update();
            }
        }
        $this->app->session()->logout();
        Redirector::to('index.php');
    }

    private function login(): void
    {
        $filtered = filter_var_array($this->request->all(), [
            'username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'password' => FILTER_UNSAFE_RAW,
            '_csrf_token' => FILTER_UNSAFE_RAW
        ], true);

        if (!CSRF::validateToken($filtered['_csrf_token'] ?? null)) {
            http_response_code(400);
            Redirector::to('index.php');
            exit('Invalid CSRF token');
        }

        $this->postUser = trim($filtered['username'] ?? '');
        $postPass = $filtered['password'] ?? '';

        if (!empty($this->postUser)) {
            $user = $this->app->dataFactory()::user()::getByUsername($this->postUser);
            $this->userAuth = $user->verifyPassword($postPass);

            if ($this->userAuth) {
                $this->afterSuccessfulLogin();
                $this->app->session()->set('isAdmin', $user->hasRole(User::USER_ROLE_ADM));
            }
        }
    }

    private function afterSuccessfulLogin(): void
    {
        session_regenerate_id(true);
        $this->app->session()->refreshExpiration();
        $this->app->session()->set('user', $this->postUser);

        $defaults = $this->app->dataFactory()::defaults();
        $defaults = $defaults::getByUsername($this->postUser) ?? $defaults::init();
        $defaults->entryDate = date('Y-m-d');
        $defaults->language = $this->app->l10n()->lang();
        Logger::instance()->info("User [{$this->postUser}] logged in");

        if ($defaults->lastVisitedAt < time() - 3600 * 24) {
            $defaults->lastVisitedUri = '';
        }

        $target = $defaults->lastVisitedUri ?: sprintf(
            'index.php?action=ledger_entries&lang=%s&filter_startDate=%s',
            $this->app->l10n()->lang(),
            date('Y-m-01')
        );

        Logger::instance()->debug("Redirecting to [{$target}]");
        Redirector::to($target);
    }

    private function renderView(): void
    {
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels($this->app->l10n(), [
                'username',
                'password',
                'login'
            ])
        );
        $this->uiData['footer']['languageSelectorHtml'] = $this->buildLanguageSelectorHtml($this->app->l10n()->lang(), ['action' => 'login']);
        $view = new LoginViewTemplate();
        if ($this->request->method() === "POST" && !$this->userAuth) {
            $errorMessage = $this->app->l10n()->l('invalid_credentials');
        }
        if ($this->request->input('needsauth', 0)) {
            $errorMessage = $this->app->l10n()->l('not_authenticated');
        }
        if ($this->request->input('expired', 0)) {
            $errorMessage = $this->app->l10n()->l('expired_session');
        }

        $view->render(array_merge($this->uiData, [
            'postUser' => $this->postUser ?? '',
            'errorMessage' => $errorMessage ?? '',
            'pagetitle' => $this->app->config()->get('title'),
        ]));
    }
}
