<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
if (!defined("ROOT_DIR")) {
    require_once __DIR__ . "/prepend.php";
}

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\CSRF;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;
use PHPLedger\Util\Redirector;
use PHPLedger\Views\ViewFactory;
$pagetitle = "Contas";

?>
<!DOCTYPE html>
<html lang="<?= l10n::html() ?>">

<head>
    <?php Html::header($pagetitle); ?>
</head>

<body>
    <div class="maingrid">
        <div id="preloader">
            <div class="spinner"></div>
        </div>
        <?php
        Html::menu();
        $object = ObjectFactory::account();
        $retval = true;
        $input_variables_filter = [
            'abertura' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
            ],
            'fecho' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => '/([0-9]{1,4})(-|\/)?([0-9]{1,2})(-|\/)?([0-9-]{1,4})/']
            ],
            'aberturaAA' => FILTER_SANITIZE_NUMBER_INT,
            'aberturaMM' => FILTER_SANITIZE_NUMBER_INT,
            'aberturaDD' => FILTER_SANITIZE_NUMBER_INT,
            'fechoAA' => FILTER_SANITIZE_NUMBER_INT,
            'fechoMM' => FILTER_SANITIZE_NUMBER_INT,
            'fechoDD' => FILTER_SANITIZE_NUMBER_INT,
            'update' => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'options' => FILTER_NULL_ON_FAILURE
            ],
            'name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'conta_id' => FILTER_VALIDATE_INT,
            'number' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'activa' => FILTER_VALIDATE_BOOLEAN,
            'typeId' => FILTER_VALIDATE_INT,
            'conta_nib' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
        ];
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $filteredInput = filter_input_array(INPUT_GET, $input_variables_filter, true);
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
                http_response_code(400);
                Redirector::to('ledger_entries.php');
            }
            $filteredInput = filter_input_array(INPUT_POST, $input_variables_filter, true);
        }
        if (($filteredInput["update"] ?? null) === "Gravar") {
            if (empty($filteredInput["name"])) {
                Html::myalert("Nome de conta invalido!");
                $retval = false;
            }
            try {
                if (empty($filteredInput["abertura"])) {
                    $openDate = new DateTime(date("Y-m-d", mktime(0, 0, 0, $filteredInput["aberturaMM"], $filteredInput["aberturaDD"], $filteredInput["aberturaAA"])));
                } else {
                    $openDate = new DateTime($filteredInput["abertura"]);
                }
            } catch (Exception $ex) {
                Html::myalert("Data de abertura invalida!");
                $retval = false;
            }
            try {
                if (empty($filteredInput["fecho"])) {
                    $closeDate = new DateTime(date("Y-m-d", mktime(0, 0, 0, $filteredInput["fechoMM"], $filteredInput["fechoDD"], $filteredInput["fechoAA"])));
                } else {
                    $closeDate = new DateTime($filteredInput["fecho"]);
                }
            } catch (Exception $ex) {
                Html::myalert("Data de fecho invalida!");
                $retval = false;
            }
            if ($retval) {
                $object->id = $filteredInput["conta_id"];
                $object->name = $filteredInput["name"];
                $object->openDate = $openDate->format("Y-m-d");
                $object->closeDate = $closeDate->format("Y-m-d");
                $object->number = $filteredInput["number"];
                $object->active = boolval($filteredInput["activa"]) ? 1 : 0;
                $object->typeId = $filteredInput["typeId"];
                $object->iban = $filteredInput["conta_nib"];
                $retval = $object->update();
            }
        }
        if (($filteredInput["update"] ?? null) === "Apagar") {
            $object->id = $filteredInput["conta_id"];
            $retval = $object->delete();
        }
        if (is_array($filteredInput) && !empty($filteredInput["update"])) {
            if ($retval) {
                if (strcasecmp($filteredInput["update"], "gravar") == 0) {
                    Html::myalert("Registo gravado");
                }
                if (strcasecmp($filteredInput["update"], "apagar") == 0) {
                    Html::myalert("Registo eliminado");
                }
            } else {
                Html::myalert("Ocorreu um erro ao criar/modificar o registo");
            }
        }
        $edit = null;
        $conta_id = is_array($filteredInput) ? $filteredInput["conta_id"] : null;
        if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($conta_id)) {
            $edit = $conta_id;
        }
        $account_list = ObjectFactory::account()::getList();
        ?>
        <div class="header" style="height: 0;"></div>
        <main>
            <div class="main" id="main">
                <div class="contas">
                    <form method="POST" action="accounts.php" name="contas">
                        <?= CSRF::inputField() ?>
                        <table class="lista contas">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Numero</th>
                                    <th>Tipo</th>
                                    <th>NIB</th>
                                    <th>Abertura</th>
                                    <th>Fecho</th>
                                    <th>Activa</th>
                                    <th>Apagar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($account_list as $account) {
                                    ?>
                                    <tr>
                                        <?php
                                        $accountView = ViewFactory::instance()->accountView($account);
                                        if (!empty($edit) && $account->id == $edit) {
                                            print $accountView->printForm();
                                        }
                                        if (empty($edit) || (!empty($edit) && $account->id != $edit)) {
                                            print $accountView->printObject();
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                }
                                if (empty($edit)) {
                                    ?>
                                    <tr>
                                        <?= ViewFactory::instance()->accountView(ObjectFactory::account())->printForm(); ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </main>
        <script>
            var test = document.createElement("input");
            try {
                test.type = "date";
            } catch (e) {
                row = document.getElementsByClassName("date-fallback");
                for (i = 0; i < row.length; i++) {
                    if (row[i].style.display == "none") {
                        row[i].style.removeProperty("display");
                    } else {
                        if (row[i].tagName == "INPUT") {
                            row[i].value = "";
                            row[i].removeAttribute("required");
                        }
                        row[i].style.display = "none";
                    }
                }
            }
        </script>
        <?php Html::footer(); ?>
    </div>
    <script>
        setTimeout(() => { document.getElementById("preloader").style.display = "none"; }, 0);
    </script>
</body>

</html>
