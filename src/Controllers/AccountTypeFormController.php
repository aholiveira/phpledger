<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\AccountType;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Util\CSRF;
use PHPLedger\Views\Templates\AccountTypeFormViewTemplate;
use Throwable;

final class AccountTypeFormController extends AbstractViewController
{
    private ?string $message = null;
    /**
     * Handle single account page (GET form or POST save/delete).
     *
     * @return void
     */
    protected function handle(): void
    {
        $filterArray = [
            "id" => FILTER_VALIDATE_INT,
            "description" => FILTER_DEFAULT,
            "savings" => FILTER_DEFAULT,
            "action" => FILTER_DEFAULT,
            "update" => FILTER_DEFAULT
        ];
        $object = $this->app->dataFactory()::accounttype();
        $filtered = filter_var_array($this->request->all(), $filterArray, true);
        $l10n = $this->app->l10n();
        if ($this->request->method() === "POST") {
            try {
                $this->handlePost($object, $filtered);
                $this->message = $l10n->l('save_success', $object->id);
                $success = true;
            } catch (Throwable $e) {
                $this->message = $e->getMessage();
            }
        }
        if ($this->request->method() === "GET") {
            $id = $filtered['id'] ?? 0;
            if ($id > 0) {
                $object = $object->getById($id);
            }
        }
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels($this->app->l10n(), [
                'id',
                'description',
                'savings',
                'save',
                'delete',
                'are_you_sure_you_want_to_delete',
                'back_to_list'
            ])
        );
        $view = new AccountTypeFormViewTemplate;
        $view->render(array_merge($this->uiData, [
            'notification' => $this->message ?? '',
            'success' => $success ?? false,
            'row' => [
                'id' => $object->id ?? '',
                'description' => $object->description ?? '',
                'savings' => $object->savings ?? false,
            ]
        ]));
    }
    private function handlePost(AccountType $object, $filtered): void
    {
        if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
            http_response_code(400);
            throw new PHPLedgerException("Falhou a validação do token. Repita a operação.");
        }
        if (strtolower($filtered['update'] ?? '') === "save"  && !$this->handleSave($object, $filtered)) {
            throw new PHPLedgerException("Ocorreu um erro ao gravar");
        }
        if (strtolower($filtered['update'] ?? '') === "delete") {
            $object->id = $filtered['id'] ?? 0;
            if ($object->id > 0 && !$object->delete()) {
                throw new PHPLedgerException("Ocorreu um erro ao eliminar");
            }
        }
    }
    private function handleSave(AccountType $object, array $filtered): bool
    {
        $object->id = (int)($filtered['id'] === false ? $object->getNextId() : $filtered['id']);
        $object->description = htmlspecialchars($filtered['description'] ?? '');
        $object->savings = empty($filtered['savings']) ? 0 : 1;
        return $object->update();
    }
}
