<?php

namespace PHPLedger\Controllers;

use PHPLedger\Util\CSRF;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Logger;
use PHPLedger\Util\Redirector;
use PHPLedger\Util\SessionManager;
use PHPLedger\Storage\ObjectFactory;

final class LoginController
{
    private string $postUser = '';
    private bool $userAuth = false;

    public function handle(): void
    {
        SessionManager::start();

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['do_logout'])) {
            $this->logout();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
        }

        $this->renderView();
    }

    private function logout(): void
    {
        if (!empty($_SESSION['user'])) {
            $defaults = ObjectFactory::defaults()::getByUsername($_SESSION['user']);
            if ($defaults !== null) {
                $defaults->lastVisitedUri = '';
                $defaults->lastVisitedAt = time();
                $defaults->update();
            }
        }
        SessionManager::logout();
        Redirector::to('index.php');
    }

    private function login(): void
    {
        $filtered = filter_input_array(INPUT_POST, [
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
            $user = ObjectFactory::user()::getByUsername($this->postUser);
            $this->userAuth = $user->verifyPassword($postPass);

            if ($this->userAuth) {
                $this->afterSuccessfulLogin();
            }
        }
    }

    private function afterSuccessfulLogin(): void
    {
        session_regenerate_id(true);
        SessionManager::refreshExpiration();
        $_SESSION['user'] = $this->postUser;

        $defaults = ObjectFactory::defaults()::getByUsername($this->postUser) ?? ObjectFactory::defaults()::init();
        $defaults->entryDate = date('Y-m-d');
        $defaults->language = L10n::$lang;
        Logger::instance()->info("User [{$this->postUser}] logged in");

        if ($defaults->lastVisitedAt < time() - 3600 * 24) {
            $defaults->lastVisitedUri = '';
        }

        $target = $defaults->lastVisitedUri ?: sprintf(
            'index.php?action=ledger_entries&lang=%s&filter_sdate=%s',
            L10n::$lang,
            date('Y-m-01')
        );

        Logger::instance()->debug("Redirecting to [{$target}]");
        Redirector::to($target);
    }

    private function renderView(): void
    {
        $view = new \PHPLedger\Views\LoginView();
        $view->render([
            'postUser' => $this->postUser,
            'userAuth' => $this->userAuth,
            'expired' => $_REQUEST['expired'] ?? 0
        ]);
    }
}
