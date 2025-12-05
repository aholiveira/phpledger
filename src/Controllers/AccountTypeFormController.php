<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\AccountTypeFormView;

final class AccountTypeFormController
{
    /**
     * Handle single account page (GET form or POST save/delete).
     *
     * @return void
     */
    public function handle(): void
    {
        $filterArray = [
            "id" => FILTER_VALIDATE_INT,
            "description" => FILTER_DEFAULT,
            "savings" => FILTER_DEFAULT,
            "action" => FILTER_DEFAULT,
            "update" => FILTER_DEFAULT
        ];
        $object = ObjectFactory::accounttype();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $filtered = filter_input_array(INPUT_POST, $filterArray, true);
            if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
                http_response_code(400);
                Redirector::to($_SERVER['REQUEST_URI']);
            }
            if (strtolower($filtered['update'] ?? '') === "gravar") {
                $object->id = (int)($filtered['id'] === false ? $object->getNextId() : $filtered['id']);
                $object->description = htmlspecialchars($filtered['description'] ?? '');
                $object->savings = empty($filtered['savings']) ? 0 : 1;
                $retval = $object->update();
            }
            if (strtolower($filtered['update'] ?? '') === "apagar") {
                $object->id = $filtered['id'] ?? 0;
                if ($object->id > 0) {
                    $retval = $object->delete();
                }
            }
            if (!$retval) {
                $message = "Ocorreu um erro na operacao.";
            } else {
                Redirector::to("index.php?action=account_types");
            }
        }
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $filtered = filter_input_array(INPUT_GET, $filterArray, true);
            $id = $filtered['id'] ?? 0;
            if ($id > 0) {
                $object = $object->getById($id);
            }
        }
        $view = new AccountTypeFormView;
        $view->render($object, isset($message) ? $message : null);
    }
}
