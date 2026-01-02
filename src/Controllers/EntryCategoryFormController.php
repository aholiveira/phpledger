<?php

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\EntryCategoryFormViewTemplate;

final class EntryCategoryFormController extends AbstractViewController
{
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
            'title'        => 'Entry Category',
            'text'         => [
                'id'          => isset($object->id) ? $object->id : '',
                'description' => $object->description ?? '',
                'active'      => ($object->id ?? 0) === 0 || ($object->active ?? 1) ? 'checked' : ''
            ],
            'parentRows'   => $parentRows,
        ]));
    }

    private function buildParentRows($object): array
    {
        $filter = ['active' => ['operator' => '=', 'value' => '1']];
        $categories = $object->getList($filter);
        $rows = [];
        foreach ($categories as $cat) {
            $rows[] = [
                'value'    => $cat->id,
                'text'     => $cat->description,
                'parentId' => $cat->parentId,
                'selected' => (int)($object->parentId ?? 0) === $cat->id
            ];
            if (count($cat->children) > 0) {
                foreach ($cat->children as $child) {
                    $rows[] = [
                        'value'    => $child->id,
                        'text'     => $child->description,
                        'parentId' => $child->parentId,
                        'selected' => $child->id === (int)$cat->parentId
                    ];
                }
            }
        }
        return ['rows' => $rows];
    }
}
