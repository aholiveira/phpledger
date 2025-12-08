<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Logger;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\AccountFormView;

final class AccountController extends AbstractViewController
{
    protected function handle(): void
    {
        if ($this->request->method() === 'POST') {
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
        $id = (int)$this->request->input('id', 0);
        $account = ($id ? ObjectFactory::account()::getById($id) : null) ?? ObjectFactory::account();
        $view = new AccountFormView();
        $view->render($this->app, [
            'account' => $account,
            'back' => $this->request->input('back', ""),
            'lang' => $this->app->l10n()->sanitizeLang($this->request->input('lang', null)),
            'action' => $this->request->input('action'),
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
        $redirectUrl = 'index.php?action=accounts';
        if (!CSRF::validateToken($this->request->input('_csrf_token', ''))) {
            Redirector::to($redirectUrl);
            return;
        }

        $action = $this->request->input('itemaction', 'save');
        if ($action === 'delete') {
            $id = (int)$this->request->input('id', 0);
            if ($id && ($a = ObjectFactory::account()::getById($id)) !== null) {
                $a->delete();
                Logger::instance()->notice("Account deleted: {$id}");
            }
            Redirector::to($redirectUrl);
            return;
        }
        // Save path
        $id = (int)$this->request->input('id', 0);
        $a = ($id ? ObjectFactory::account()::getById($id) : null) ?? ObjectFactory::account();
        if ($a->id === null) {
            $a->id = $a->getNextId();
        }
        // Basic assignment and server-side required validation
        $a->name = trim((string) ($this->request->input('name', '')));
        $a->number = trim((string) ($this->request->input('number', '')));
        $a->typeId = (int) ($this->request->input('typeId', 0));
        $a->iban = trim((string) ($this->request->input('iban', '')));
        $a->swift = trim((string) ($this->request->input('swift', '')));
        $a->openDate = trim((string) ($this->request->input('openDate', date('Y-m-d'))));
        $a->closeDate = trim((string) ($this->request->input('closeDate', '')));
        $a->activa = $this->request->input('activa', 0) === 0 ? 0 : 1;
        $a->grupo = (int) ($this->request->input('grupo', 0));

        // simple required validation for name (keep minimal as requested)
        $errors = [];
        if ($a->name === '') {
            $errors[] = 'name';
        }
        if ($a->update()) {
            Logger::instance()->info("Account saved: " . ($a->id ?? '(new)'));
            Redirector::to($redirectUrl);
        } else {
            Logger::instance()->info("Error saving account: " . ($a->id ?? '(new)'));
            $errors[] = 'other';
        }

        $view = new AccountFormView();
        $view->render($this->app, [
            'account' => $a,
            'lang' => $this->app->l10n()->sanitizeLang($this->request->input('lang')),
            'errors' => $errors,
            'action' => $this->request->input('action')
        ]);
    }
}
