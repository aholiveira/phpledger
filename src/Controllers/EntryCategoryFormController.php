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

use PHPLedger\Views\Templates\EntryCategoryFormViewTemplate;

final class EntryCategoryFormController extends AbstractViewController
{
    /**
     * Handle entry category form display.
     */
    protected function handle(): void
    {
        $object = $this->app->dataFactory()::EntryCategory();
        $input = $this->request->all();
        $id = isset($input['id']) && is_numeric($input['id']) ? (int)$input['id'] : 0;
        if ($id > 0) {
            $object = $object->getById($id);
        }

        $parentRows = $this->buildParentRows($object);
        $template = new EntryCategoryFormViewTemplate();
        $template->render(array_merge($this->uiData, [
            'title' => 'Entry Category',
            'text' => [
                'id' => isset($object->id) ? $object->id : '',
                'description' => $object->description ?? '',
                'active' => ($object->id ?? 0) === 0 || ($object->active ?? 1) ? 'checked' : ''
            ],
            'parentRows' => $parentRows,
        ]));
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
