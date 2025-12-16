<?php

namespace PHPLedger\Views\Templates;

use PHPLedger\Util\Html;
use PHPLedger\Views\Templates\AbstractViewTemplate;

class ResetPasswordViewTemplate extends AbstractViewTemplate
{
    public function render(array $data): void
    {
        extract($data, EXTR_SKIP);
        $pagetitle = "Recuperação de password";
?>
        <!DOCTYPE html>
        <html lang="<?= $lang ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div id="login">
                <h1><?= $apptitle ?></h1>
                <p style="color:<?= $success ? "green" : "red" ?>;"><?= $message ?></p>
                <?php if ($success === false): ?>
                    <form id="resetForm" method="POST">
                        <input type="hidden" name="tokenId" value="<?= htmlspecialchars($tokenId ?? '') ?>">
                        <input type="hidden" name="action" value="<?= $action ?>">
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
<?php
    }
}
