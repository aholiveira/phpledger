<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\EntryCategoryFormView;
use PHPLedger\Views\ViewFactory;

final class EntryCategoryFormController extends AbstractViewController
{
    protected function handle(): void
    {
        $object = ObjectFactory::EntryCategory();
        $filterArray = ['id' => FILTER_VALIDATE_INT];
        $filtered = filter_var_array($this->request->all(), $filterArray, true);
        $id = is_numeric($filtered['id'] ?? 0) ? (int)$filtered['id'] : 0;
        if ($id > 0) {
            $object = $object->getById($id);
        }
        $viewer = ViewFactory::instance()->entryCategoryView($this->app, $object);
        $form = $viewer->printForm();
        $view = new EntryCategoryFormView;
        $view->render($this->app, $form, $object->id > 0, $this->request->input('action'));
    }
}
