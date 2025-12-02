<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
use PHPLedger\Util\SessionManager;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Logger;
use PHPLedger\Views\AccountFormView;

final class AccountController
{
    /**
     * Handle single account page (GET form or POST save/delete).
     *
     * @return void
     */
    public function handle(): void
    {
        SessionManager::start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPost();
            return;
        }

        $this->renderForm();
    }

    /**
     * Render add/edit form.
     *
     * @return void
     */
    private function renderForm(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $account = ($id ? ObjectFactory::account()::getById($id) : null) ?? ObjectFactory::account();
        $view = new AccountFormView();
        $view->render([
            'account' => $account,
            'lang' => $_GET['lang'] ?? L10n::$lang,
            'errors' => []
        ]);
    }

    /**
     * Process POST (save or delete).
     *
     * @return void
     */
    private function processPost(): void
    {
        $action = $_POST['action'] ?? 'save';

        if (!CSRF::validateToken($_POST['_csrf_token'] ?? '')) {
            Redirector::to('index.php?action=accounts');
            return;
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id) {
                if (($a = ObjectFactory::account()::getById($id)) !== null) {
                    $a->delete();
                    Logger::instance()->notice("Account deleted: {$id}");
                }
            }
            Redirector::to('index.php?action=accounts');
            return;
        }

        // Save path
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $a = ($id ? ObjectFactory::account()::getById($id) : null) ?? ObjectFactory::account();
        $a->id = $a->id ?? $a->getNextId();
        // Basic assignment and server-side required validation
        $a->name = trim((string) ($_POST['name'] ?? ''));
        $a->number = trim((string) ($_POST['number'] ?? ''));
        $a->typeId = (int) ($_POST['typeId'] ?? 0);
        $a->iban = trim((string) ($_POST['iban'] ?? ''));
        $a->swift = trim((string) ($_POST['swift'] ?? ''));
        $a->openDate = trim((string) ($_POST['openDate'] ?? date('Y-m-d')));
        $a->closeDate = trim((string) ($_POST['closeDate'] ?? ''));
        $a->activa = isset($_POST['activa']) ? 1 : 0;
        $a->grupo = (int) ($_POST['grupo'] ?? 0);

        // simple required validation for name (keep minimal as requested)
        $errors = [];
        if ($a->name === '') {
            $errors[] = 'name';
        }
        if ($a->update()) {
            Logger::instance()->info("Account saved: " . ($a->id ?? '(new)'));
            Redirector::to('index.php?action=accounts');
        } else {
            Logger::instance()->info("Error saving account: " . ($a->id ?? '(new)'));
            $errors[] = 'other';
        }

        $view = new AccountFormView();
        $view->render([
            'account' => $a,
            'lang' => $_POST['lang'] ?? L10n::$lang,
            'errors' => $errors
        ]);
    }
}
