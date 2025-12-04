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
use PHPLedger\Util\CSRF;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\UpdateStorageView;

final class UpdateStorageController
{
    public function handle(): void
    {
        Config::init(__DIR__ . '/config.json');

        $data_storage = ObjectFactory::dataStorage();
        $updateResult = null;
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
                http_response_code(400);
                Redirector::to('index.php?action=update');
            }
            $action = $_POST['action'] ?? null;
            if ($action === 'update_db') {
                $updateResult = $data_storage->update();
                if ($updateResult && !headers_sent()) {
                    header("Refresh: 8; URL=index.php");
                }
            }
        }

        $needsUpdate = !$data_storage->check();
        $message = nl2br(htmlspecialchars($data_storage->message(), ENT_QUOTES, 'UTF-8'));
        $pagetitle = L10n::l('update_needed');
        $view = new UpdateStorageView;
        $view->render($pagetitle, $needsUpdate, $updateResult, $message);
    }
}
