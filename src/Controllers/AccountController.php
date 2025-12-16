<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\Account;
use PHPLedger\Views\Templates\AccountFormViewTemplate;

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
            $id = (int)$this->request->input('id', 0);
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
        $this->uiData['label'] = array_merge($this->uiData['label'], $this->buildL10nLabels($this->app->l10n(), [
            'back_to_balances',
            'back_to_list',
            'name',
            'number',
            'type',
            'iban',
            'swift',
            'openDate',
            'closeDate',
            'active',
            'save',
            'delete',
            'check_your_data',
            'name_required',
        ]));
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
                'activa' => ($this->account->activa ?? 1) === 1,
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
        if (!$this->app->csrf()->validateToken($this->request->input('_csrf_token', ''))) {
            $this->app->redirector()->to($redirectUrl);
            return;
        }

        $action = $this->request->input('itemaction', 'save');
        if ($action === 'delete') {
            $id = (int)$this->request->input('id', 0);
            if ($id && ($a = $this->app->dataFactory()::account()::getById($id)) !== null) {
                $a->delete();
                $this->app->logger()->notice("Account deleted: {$id}");
            }
            $this->app->redirector()->to($redirectUrl);
            return;
        }
        // Save path
        $id = (int)$this->request->input('id', 0);
        $a = ($id ? $this->app->dataFactory()::account()::getById($id) : null) ?? $this->app->dataFactory()::account();
        if ($a->id === null) {
            $a->id = $a->getNextId($this->app->dataFactory());
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
        if ($a->name === '') {
            $this->errors[] = 'name';
        }
        if ($a->update()) {
            $this->app->logger()->info("Account saved: " . ($a->id ?? '(new)'));
            $this->app->redirector()->to($redirectUrl);
        } else {
            $this->app->logger()->info("Error saving account: " . ($a->id ?? '(new)'));
            $this->errors[] = 'other';
        }

        $this->account = $a;
    }
}
