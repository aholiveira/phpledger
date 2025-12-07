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
use PHPLedger\Util\Config;
use PHPLedger\Util\ConfigPath;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\UpdateStorageView;

final class UpdateStorageController extends AbstractViewController
{
    protected function handle(): void
    {
        Config::init(ConfigPath::get());
        $dataStorage = ObjectFactory::dataStorage();
        $updateResult = null;
        if ($this->request->method() === "POST") {
            if (!CSRF::validateToken($this->request->input('_csrf_token', null))) {
                http_response_code(400);
                Redirector::to('index.php?action=update');
            }
            $action = $this->request->input('action', null);
            if ($action === 'update_db') {
                $updateResult = $dataStorage->update();
                if ($updateResult && !headers_sent()) {
                    header("Refresh: 8; URL=index.php");
                }
            }
        }

        $needsUpdate = !$dataStorage->check();
        $message = nl2br(htmlspecialchars($dataStorage->message(), ENT_QUOTES, 'UTF-8'));
        $pagetitle = $this->app->l10n()->l('update_needed');
        $view = new UpdateStorageView;
        $view->render($this->app, $pagetitle, $needsUpdate, $updateResult, $message);
    }
}
