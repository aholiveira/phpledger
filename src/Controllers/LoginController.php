<?php

/**
 * Controller for handling user login and logout.
 *
 * Processes login POST requests with CSRF validation, sets session data,
 * handles logout, and renders the login view with appropriate messages.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Exceptions\InvalidCsrfTokenException;
use PHPLedger\Views\Templates\LoginViewTemplate;

final class LoginController extends AbstractViewController
{
    private string $postUser = '';
    private bool $userAuth = false;

    /**
     * Handle login/logout requests and render the login view.
     */
    public function handle(): void
    {
        if ($this->request->isGet() && $this->request->input('action', '') === 'logout') {
            $this->logout();
            return;
        }

        if ($this->request->isPost()) {
            $this->login();
        }

        $this->renderView();
    }

    /**
     * Log out the current user.
     */
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
        $this->app->redirector()->to('index.php');
    }

    /**
     * Process login POST request with CSRF and password validation.
     */
    private function login(): void
    {
        $filtered = filter_var_array($this->request->all(), [
            'username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'password' => FILTER_UNSAFE_RAW,
            '_csrf_token' => FILTER_UNSAFE_RAW
        ], true);

        if (!$this->app->csrf()->validateToken($filtered['_csrf_token'] ?? null)) {
            http_response_code(400);
            $this->app->redirector()->to('index.php');
            throw new InvalidCsrfTokenException('Invalid CSRF token');
        }

        $this->postUser = trim($filtered['username'] ?? '');
        $postPass = $filtered['password'] ?? '';

        if (!empty($this->postUser)) {
            $user = $this->app->dataFactory()->user()::getByUsername($this->postUser);
            $this->userAuth = $user->verifyPassword($postPass);
            if ($this->userAuth) {
                $this->afterSuccessfulLogin();
                $this->setSessionUserInfo($user);
            }
        }
    }

    /**
     * Set session variables for the authenticated user.
     */
    private function setSessionUserInfo(User $user): void
    {
        $session = $this->app->session();
        $session->set('isAdmin', $user->hasRole(User::USER_ROLE_ADM));
        $session->set('username', $user->getProperty('username'));
        $session->set('firstName', $user->getProperty('firstName'));
        $session->set('lastName', $user->getProperty('lastName'));
        $session->set('fullName', $user->getProperty('fullName'));
    }

    /**
     * Handle post-login actions, session updates, and redirect.
     */
    private function afterSuccessfulLogin(): void
    {
        session_regenerate_id(true);
        $this->app->session()->refreshExpiration();
        $this->app->session()->set('user', $this->postUser);

        $defaults = $this->app->dataFactory()::defaults();
        $defaults = $defaults::getByUsername($this->postUser) ?? $defaults::init();
        $defaults->entryDate = date('Y-m-d');
        $defaults->language = $this->app->l10n()->lang();
        $this->app->logger()->info("User [{$this->postUser}] logged in");

        if ($defaults->lastVisitedAt < time() - 3600 * 24) {
            $defaults->lastVisitedUri = '';
        }

        $target = $defaults->lastVisitedUri ?: sprintf(
            'index.php?action=ledger_entries&lang=%s&filter_startDate=%s',
            $this->app->l10n()->lang(),
            date('Y-m-01')
        );

        $this->app->logger()->debug("Redirecting to [{$target}]");
        $this->app->redirector()->to($target);
    }

    /**
     * Render the login view with any error messages.
     */
    private function renderView(): void
    {
        $l10n = $this->app->l10n();
        $this->uiData['footer']['languageSelectorHtml'] = $this->buildLanguageSelectorHtml($l10n, $l10n->lang(), ['action' => 'login']);

        $view = new LoginViewTemplate();
        if ($this->request->isPost()  && !$this->userAuth) {
            $errorMessage = $l10n->l('invalid_credentials');
        }
        if ($this->request->input('needsauth', 0)) {
            $errorMessage = $l10n->l('not_authenticated');
        }
        if ($this->request->input('expired', 0)) {
            $errorMessage = $l10n->l('expired_session');
        }

        $view->render(array_merge($this->uiData, [
            'postUser' => $this->postUser ?? '',
            'errorMessage' => $errorMessage ?? '',
            'pagetitle' => $this->app->config()->get('title'),
        ]));
    }
}
