<?php

namespace PHPLedger\Views;

use PHPLedger\Util\Config;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class ResetPasswordView
{
    public function render(?string $tokenId, bool $success, string $message): void
    {
        $pagetitle = "Recuperação de password";
?>
        <!DOCTYPE html>
        <html lang="<?= L10n::html(); ?>">

        <head>
            <title><?= Html::title($pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body>
            <div id="login">
                <h1><?= Config::get("title"); ?></h1>

                <?php if ($success === false): ?>
                    <p style="color:red;"><?= $message ?></p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p style="color:green;"><?= $message ?></p>
                <?php endif; ?>

                <?php if ($success === false): ?>
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
<?php
    }
}
