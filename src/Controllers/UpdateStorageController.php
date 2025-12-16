<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\UpdateStorageViewTemplate;

final class UpdateStorageController extends AbstractViewController
{
    protected function handle(): void
    {
        $dataStorage = $this->app->dataFactory()->dataStorage();
        $updateResult = null;
        if ($this->request->method() === "POST") {
            if (!$this->app->csrf()->validateToken($this->request->input('_csrf_token', null))) {
                http_response_code(400);
                $this->app->redirector()->to('index.php?action=update');
            }
            $action = $this->request->input('action', null);
            if ($action === 'update') {
                $updateResult = $dataStorage->update();
                if ($updateResult && !headers_sent()) {
                    $this->app->redirector()->to("index.php", 8);
                    $showSection = "update_sucess";
                }
            }
        }
        $l10n = $this->app->l10n();
        $needsUpdate = !$dataStorage->check();
        if ($this->request->method() === "GET") {
            if ($needsUpdate) {
                $showSection = "needs_update";
            } else {
                $showSection = "storage_is_ok";
            }
        }
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels(
                $l10n,
                [
                    'db_needs_update',
                    'cannot_use_app',
                    'start_update',
                    'do_update',
                    'db_ok',
                    'go_login',
                    'login_screen',
                    'db_updated',
                    'redirecting',
                    'update_fail',
                    'error_msg'
                ]
            )
        );
        $view = new UpdateStorageViewTemplate;
        $view->render(array_merge($this->uiData, [
            'pagetitle' => $l10n->l('update_needed'),
            'message' => $dataStorage->message(),
            'needsUpdate' => $needsUpdate,
            'updateResult' => $updateResult,
            'showSection' => $showSection ?? '',
        ]));
    }
}
