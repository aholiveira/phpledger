<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\AccountType;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\AccountTypeFormView;

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
        $object = ObjectFactory::accounttype();
        $filtered = filter_var_array($this->request->all(), $filterArray, true);
        if ($this->request->method() === "POST") {
            $this->handlePost($object, $filtered);
        }
        if ($this->request->method() === "GET") {
            $id = $filtered['id'] ?? 0;
            if ($id > 0) {
                $object = $object->getById($id);
            }
        }
        $view = new AccountTypeFormView;
        $view->render($this->app, $object, $this->message, $this->request->input('action'));
    }
    private function handlePost(AccountType $object, $filtered)
    {
        $retval = false;
        if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
            http_response_code(400);
            $this->message = "Falhou a validação do token. Repita a operação.";
            return;
        }
        if (strtolower($filtered['update'] ?? '') === "gravar") {
            $retval = $this->handleSave($object, $filtered);
        }
        if (strtolower($filtered['update'] ?? '') === "apagar") {
            $object->id = $filtered['id'] ?? 0;
            if ($object->id > 0) {
                $retval = $object->delete();
            }
        }
        if ($retval) {
            Redirector::to("index.php?action=account_types");
        } else {
            $this->message = "Ocorreu um erro na operação.";
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
