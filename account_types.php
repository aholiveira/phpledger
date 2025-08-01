<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */
include __DIR__ . "/contas_config.php";
$pagetitle = "Tipo de contas";
$message = "";
$retval = false;
$object = $object_factory->accounttype();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!CSRF::validateToken($_POST['_csrf_token'] ?? null)) {
        http_response_code(400);
        Redirector::to('ledger_entries.php');
    }
    if (stristr($_POST["update"], "gravar")) {
        $object->id = filter_input(INPUT_POST, "tipo_id", FILTER_VALIDATE_INT);
        $object->description = filter_input(INPUT_POST, "tipo_desc", FILTER_DEFAULT);
        $object->savings = filter_has_var(INPUT_POST, "savings") ? 1 : 0;
        $retval = $object->update();
    } else {
        $object->id = filter_input(INPUT_POST, "tipo_id", FILTER_VALIDATE_INT);
        if ($object->id > 0) {
            $retval = $object->delete();
        }
    }
    if (!$retval) {
        $message = "Ocorreu um erro na operacao.";
    } else {
        if (!headers_sent()) {
            header("Location: account_types_list.php" . (isset($object->id) ? "?tipo_id={$object->id}" : ""));
            exit;
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $object->id = $object->getNextId();
    if (filter_has_var(INPUT_GET, "tipo_id")) {
        $tipo_id = filter_input(INPUT_GET, "tipo_id", FILTER_VALIDATE_INT);
        if ($tipo_id > 0) {
            $object = $object->getById($tipo_id);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <?php include "header.php"; ?>
</head>

<body>
    <?php
    if (strlen($message)) {
        Html::myalert($message);
    }
    ?>
    <div class="maingrid" id="maingrid">
        <?php
        include ROOT_DIR . "/menu_div.php";
        ?>
        <div class="header" style="height: 0;"></div>
        <div id="main" class="main">
            <form method="POST" action="account_types.php">
                <?= CSRF::inputField() ?>
                <table class="single_item account_type_form">
                    <tr>
                        <td>ID</td>
                        <td data-label="ID">
                            <input type="text" readonly size="4" name="tipo_id" value="<?= $object->id ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Descri&ccedil;&atilde;o</td>
                        <td data-label="Descri&ccedil;&atilde;o">
                            <input type="text" size="30" maxlength="30" name="tipo_desc"
                                value="<?= $object->description ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>Poupan&ccedil;a</td>
                        <td data-label="Poupan&ccedil;a">
                            <input type="checkbox" name="savings" <?= $object->savings ? "checked" : "" ?>>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="update" value="Gravar"></td>
                        <td><input type="submit" name="update" value="Apagar"
                                onclick="return confirm('Pretende apagar o registo?');"></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>