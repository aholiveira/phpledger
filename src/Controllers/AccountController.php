<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\Account;
use PHPLedger\Views\Templates\AccountFormViewTemplate;
use Throwable;

final class AccountController extends AbstractViewController
{
    protected array $errors;
    protected Account $account;
    protected function handle(): void
    {
        if ($this->request->method() === 'POST') {
            $this->processPost();
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
        if (!(($this->account ?? null) instanceof Account)) {
            $id = (int)$this->request->input('id');
            $this->account = ($id ? $this->app->dataFactory()->account()::getById($id) : null) ?? $this->app->dataFactory()->account();
        }
        $view = new AccountFormViewTemplate();
        $accountTypes = [];
        $accountTypes[] = [
            'value' => 0,
            'parentId' => null,
            'text' => "",
            'selected' => (($this->account->typeId ?? 0) === 0)
        ];
        foreach ($this->app->dataFactory()->accounttype()::getList() as $r) {
            $accountTypes[] = [
                'value' => $r->id,
                'parentId' => null,
                'text' => $r->description,
                'selected' => (($this->account->typeId ?? 0) === $r->id)
            ];
        }
        $view->render(array_merge($this->uiData, [
            'account' => $this->account,
            'back' => $this->request->input('back', ""),
            'lang' => $this->app->l10n()->html(),
            'pagetitle' => $this->app->l10n()->l('accounts'),
            'errors' => $this->errors ?? [],
            'accountTypes' => $accountTypes,
            'text' => [
                'id' => $this->account->id ?? 0,
                'name' => $this->account->name ?? '',
                'number' => $this->account->number ?? '',
                'iban' => $this->account->iban ?? '',
                'swift' => $this->account->swift ?? '',
                'openDate' => $this->account->openDate ?? date("Y-m-d"),
                'closeDate' => $this->account->closeDate ?? date("Y-m-d", 0),
                'active' => ($this->account->active ?? 1) === 1,
            ]
        ]));
    }

    /**
     * Process POST (save or delete).
     *
     * @return void
     */
    private function processPost(): void
    {
        $redirectUrl = 'index.php?action=accounts';
        $userName = $this->currentUser?->getProperty('userName', '');

        $account = null;

        if (!$this->isCsrfValid() || !$this->canCurrentUserWrite($userName)) {
            $this->app->redirector()->to($redirectUrl);
            return;
        }

        $action = $this->request->input('itemaction', 'save');

        if ($action === 'delete') {
            $this->handleDelete($userName);
        } else {
            $account = $this->handleSave($userName);
        }

        $this->account = $account ?? $this->account;
        $this->app->redirector()->to($redirectUrl);
    }

    private function isCsrfValid(): bool
    {
        return $this->app->csrf()->validateToken($this->request->input('_csrf_token', ''));
    }

    private function canCurrentUserWrite(string $userName): bool
    {
        if (!$this->permissions?->canWrite()) {
            $this->errors[] = 'forbidden';
            $this->app->logger()->warning("User [{$userName}] attempted write account without permission");
            return false;
        }
        return true;
    }

    private function handleDelete(string $userName): void
    {
        $id = (int)$this->request->input('id', 0);
        if ($id && ($a = $this->app->dataFactory()::account()::getById($id)) !== null) {
            $a->delete();
            $this->app->logger()->notice("Account [{$id}] deleted by user [{$userName}]");
        }
    }

    private function handleSave(string $userName): Account
    {
        $id = (int)$this->request->input('id');
        $a = ($id ? $this->app->dataFactory()::account()::getById($id) : null) ?? $this->app->dataFactory()::account();
        $fields = [
            'name' => '',
            'number' => '',
            'typeId' => 0,
            'iban' => '',
            'swift' => '',
            'openDate' => date('Y-m-d'),
            'closeDate' => '',
            'active' => 0,
            'grupo' => 0,
        ];

        foreach ($fields as $key => $default) {
            $value = $this->request->input($key, $default);
            $a->$key = is_int($default) ? (int)$value : trim((string)$value);
        }

        $a->active = $this->request->input('active', 0) === 0 ? 0 : 1;

        if ($a->name === '') {
            $this->errors[] = 'name';
        }
        if ($a->update()) {
            $this->app->logger()->info("Account [" . ($a->id ?? '(new)') . "] saved by user [{$userName}]");
        } else {
            $this->app->logger()->info("Error saving account [" . ($a->id ?? '(new)') . "] by user [{$userName}]: " . $a->errorMessage());
            $this->errors[] = 'other';
        }
        return $a;
    }
}
