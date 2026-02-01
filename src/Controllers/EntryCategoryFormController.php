<?php

/**
 * Controller for creating or editing entry categories.
 *
 * Fetches the requested category (if any), builds parent category options,
 * and renders the entry category form view template.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Views\Templates\EntryCategoryFormViewTemplate;

final class EntryCategoryFormController extends AbstractFormController
{
    protected array $filterArray = [
        "id" => FILTER_VALIDATE_INT,
        "description" => FILTER_DEFAULT,
        "parentId" => FILTER_VALIDATE_INT,
        "fixedCost" => FILTER_DEFAULT,
        "active" => FILTER_DEFAULT,
        "update" => FILTER_DEFAULT
    ];

    protected function setupObject(): DataObjectInterface
    {
        return $this->app->dataFactory()->EntryCategory();
    }

    protected function renderView(DataObjectInterface $object, bool $success): void
    {
        $parentRows = $this->buildParentRows($object);
        $template = new EntryCategoryFormViewTemplate();
        $template->render(array_merge($this->uiData, [
            'title' => 'Entry Category',
            'text' => [
                'id' => isset($object->id) ? $object->id : '',
                'description' => $object->description ?? '',
                'fixedCost' => ($object->id ?? 0) === 0 || ($object->fixedCost ?? 1) ? 'checked' : '',
                'active' => ($object->id ?? 0) === 0 || ($object->active ?? 1) ? 'checked' : ''
            ],
            'parentRows' => $parentRows,
        ]));
    }
    /**
     * No-op - object save is handled by the list controller
     */
    protected function handleSave(DataObjectInterface $object, array $filtered): bool
    {
        return false;
    }
    /**
     * Build parent category rows for selection.
     *
     * @param object $object The current entry category object
     * @return array Rows for parent category select
     */
    private function buildParentRows(object $object): array
    {
        $filter = ['active' => ['operator' => '=', 'value' => '1']];
        $categories = $object->getList($filter);
        $rows = [];

        foreach ($categories as $cat) {
            $rows[] = [
                'value' => $cat->id,
                'text' => $cat->description,
                'parentId' => $cat->parentId,
                'selected' => (int)($object->parentId ?? 0) === $cat->id
            ];

            if (!empty($cat->children)) {
                foreach ($cat->children as $child) {
                    $rows[] = [
                        'value' => $child->id,
                        'text' => $child->description,
                        'parentId' => $child->parentId,
                        'selected' => $child->id === (int)$cat->parentId
                    ];
                }
            }
        }

        return ['rows' => $rows];
    }
}
