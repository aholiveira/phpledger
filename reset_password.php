<?php
if (!defined("ROOT_DIR")) {
    include_once __DIR__ . "/prepend.php";
}

use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

$pagetitle = "Redefinição de palavra-passe";
$error = null;
$success = null;
$refreshHeader = "Refresh: 8; URL=index.php";
$tokenId = filter_input(INPUT_GET, "tokenId", FILTER_SANITIZE_ENCODED);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tokenId = filter_input(INPUT_POST, "tokenId", FILTER_SANITIZE_ENCODED);
}

/* Validate token */
if (empty($tokenId)) {
    header($refreshHeader);
    $error = "Token em falta. Será redirecionado para a página inicial.";
} else {
    $user = ObjectFactory::user()::getByToken($tokenId);
    if (!$user instanceof user || !$user->isTokenValid($tokenId)) {
        header($refreshHeader);
        $error = "Token inválido ou expirado. Será redirecionado para a página inicial.";
    }
}

/* POST handler */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $password = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
    $verifyPassword = filter_input(INPUT_POST, "verifyPassword", FILTER_UNSAFE_RAW);

    if (!$password || !$verifyPassword) {
        $error = "Tem que indicar uma palavra-passe.";
    } elseif ($password !== $verifyPassword) {
        $error = "As palavras-passe não coincidem.";
    } else {
        if ($user instanceof user && $user->isTokenValid($tokenId)) {
            $user->setPassword($password);
            $user->setToken('');
            $user->setTokenExpiry(null);
            if ($user->update()) {
                header($refreshHeader);
                $success = "Palavra-passe alterada com sucesso. Será redirecionado para a página inicial.";
            } else {
                $error = "Erro ao atualizar a palavra-passe.";
            }
        } else {
            header($refreshHeader);
            $error = "Token inválido ou expirado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= L10n::html(); ?>">

<head>
    <?php Html::header($pagetitle); ?>
</head>

<body>
    <div id="login">
        <h1><?= Config::get("title"); ?></h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form id="resetForm" method="POST" action="reset_password.php">
                <input type="hidden" name="tokenId" value="<?= htmlspecialchars($tokenId ?? '') ?>">
                <div class="formgrid">
                    <p>Redefinição de palavra-passe</p>
                    <label for="password">Nova palavra-passe:</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>

                    <label for="verifyPassword">Confirmar palavra-passe:</label>
                    <input id="verifyPassword" type="password" name="verifyPassword" autocomplete="new-password" required>

                    <input id="submitButton" type="submit" value="Repor" class="submit" disabled>
                    <p id="errorMsg" style="color:red;"></p>
                </div>
            </form>
        <?php endif; ?>

    </div>
    <script>
        const password = document.getElementById('password');
        const verify = document.getElementById('verifyPassword');
        const submitBtn = document.getElementById('submitButton');
        const errorMsg = document.getElementById('errorMsg');

        function validatePasswords() {
            if (password.value && verify.value && password.value === verify.value) {
                submitBtn.disabled = false;
                errorMsg.textContent = "";
            } else {
                submitBtn.disabled = true;
                if (verify.value && password.value !== verify.value) {
                    errorMsg.textContent = "As palavras-passe não coincidem";
                } else {
                    errorMsg.textContent = "";
                }
            }
        }
        password.addEventListener('input', validatePasswords);
        verify.addEventListener('input', validatePasswords);
        password.addEventListener('change', validatePasswords);
        verify.addEventListener('change', validatePasswords);
        password.addEventListener('paste', () => setTimeout(validatePasswords, 0));
        verify.addEventListener('paste', () => setTimeout(validatePasswords, 0));
    </script>
</body>

</html>
