<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use Exception;
use PHPLedger\Domain\EntryCategory;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\EntryCategoryListView;

final class EntryCategoryListController extends AbstractViewController
{
    private EntryCategory $object;

    protected function handle(): void
    {
        $success = false;
        try {
            if ($this->request->method() == "POST") {
                $filterArray = [
                    "id" => FILTER_VALIDATE_INT,
                    "description" => FILTER_DEFAULT,
                    "parentId" => FILTER_VALIDATE_INT,
                    "active" => FILTER_DEFAULT,
                    "update" => FILTER_DEFAULT
                ];
                $filtered = filter_var_array($this->request->all(), $filterArray, true);
                $action = strtolower($filtered["update"] ?? "");
                $this->object = ObjectFactory::entryCategory();
                if ($action === "gravar") {
                    $success = $this->handleUpdate($filtered);
                }
                if ($action === "apagar") {
                    $success = $this->handleDelete($filtered);
                }
                if (!$success) {
                    throw new PHPLedgerException("Ocorreu um erro na operacao");
                }
                $message = "Registo {$action}. ID: {$this->object->id}";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $view = new EntryCategoryListView;
        $view->render($this->app, isset($message) ? $message : "", $success, $this->request->input('action'));
    }

    private function handleUpdate(array $filtered): bool
    {
        $this->object->id = (int)$filtered['id'] ?? 0;
        $this->object->description = $filtered['description'] ?? "";
        if (isset($filtered['parentId'])) {
            $this->object->parentId = $filtered['parentId'] !== 0 ? $filtered['parentId'] : null;
        }
        if ($this->object->parentId === $this->object->id) {
            throw new PHPLedgerException("N&atilde;o pode colocar uma categoria como ascendente dela propria!");
        }
        if (isset($filtered['active'])) {
            $this->object->active = strtolower($filtered['active']) === "on" ? 1 : 0;
        }
        if ($this->object->validate()) {
            return $this->object->update();
        } else {
            throw new PHPLedgerException("Dados inv&aacute;lidos. Por favor verifique.");
        }
    }
    private function handleDelete(array $filtered): bool
    {
        if (isset($filtered['id'])) {
            $this->object->id = $filtered['id'];
            return $this->object->delete();
        }
        return false;
    }
}
