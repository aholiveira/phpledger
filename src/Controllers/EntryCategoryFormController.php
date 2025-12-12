<?php

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\Templates\EntryCategoryFormViewTemplate;

final class EntryCategoryFormController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = ObjectFactory::EntryCategory();
        $input = $this->request->all();
        $id = isset($input['id']) && is_numeric($input['id']) ? (int)$input['id'] : 0;
        if ($id > 0) {
            $object = $object->getById($id);
        }
        $parentRows = $this->buildParentRows($object);
        $template = new EntryCategoryFormViewTemplate();
        $template->render([
            'title'        => 'Entry Category',
            'app'          => $this->app,
            'action'       => $this->request->input('action', 'entry_type'),
            'isAdmin'      => $this->app->session()->get('isAdmin', false),
            'label'        => [
                'id'          => 'ID',
                'parentId'    => 'Categoria',
                'description' => 'Descrição',
                'active'      => 'Activa',
                'delete'      => 'Eliminar',
                'save'        => 'Guardar',
                'confirm'     => 'Are you sure you want to delete this?'

            ],
            'text'         => [
                'id'          => isset($object->id) ? $object->id : "",
                'description' => $object->description ?? '',
                'active'      => isset($object->active) && $object->active ? 'checked' : ''
            ],
            'parentRows'   => $parentRows,
        ]);
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
