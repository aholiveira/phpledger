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
use PHPLedger\Util\NumberUtil;
use PHPLedger\Views\Templates\EntryCategoryListViewTemplate;

final class EntryCategoryListController extends AbstractViewController
{
    private EntryCategory $object;

    protected function handle(): void
    {
        $success = false;
        try {
            if ($this->request->method() === "POST") {
                $filterArray = [
                    "id" => FILTER_VALIDATE_INT,
                    "description" => FILTER_DEFAULT,
                    "parentId" => FILTER_VALIDATE_INT,
                    "active" => FILTER_DEFAULT,
                    "update" => FILTER_DEFAULT
                ];
                $filtered = filter_var_array($this->request->all(), $filterArray, true);
                $action = strtolower($filtered["update"] ?? "");
                $this->object = $this->app->dataFactory()->entryCategory();
                if ($action === "save") {
                    $success = $this->handleUpdate($filtered);
                }
                if ($action === "delete") {
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
        $object = $this->app->dataFactory()->entryCategory();
        $objectList = $object->getList();
        $rows = [];

        foreach ($objectList as $category) {
            if ($category->id > 0) {
                $rows[] = $this->makeRow($category);
            }
            foreach ($category->children as $child) {
                $rows[] = $this->makeRow($child);
            }
        }
        $template = new EntryCategoryListViewTemplate();
        $template->render(array_merge($this->uiData, [
            'title'    => 'Tipos de movimentos',
            'app'      => $this->app,
            'object'   => $object,
            'lang'     => $this->app->l10n()->html(),
            'action'   => $this->request->input('action', 'entry_types'),
            'isAdmin'  => $this->app->session()->get('isAdmin', false),
            'message'  => htmlentities($message ?? ''),
            'success'  => $success ?? false,
            'rows'     => $rows,
        ]));
    }

    private function handleUpdate(array $filtered): bool
    {
        $this->object->id = empty($filtered['id']) ? $this->object->getNextId() : $filtered['id'];
        $this->object->description = $filtered['description'] ?? "";
        if (isset($filtered['parentId'])) {
            $this->object->parentId = $filtered['parentId'] !== 0 ? $filtered['parentId'] : 0;
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
    private function makeRow(EntryCategory $c): array
    {
        return [
            'href'        => ($c->id ?? 0) > 0 ? "index.php?action=entry_type&id={$c->id}" : "",
            'id'          => $c->id ?? "",
            'parentId'      => $c->parentId,
            'description' => $c->description ?? '',
            'amount'      => NumberUtil::normalize(abs($c->getBalance())),
            'active'      => $c->active
        ];
    }
}
