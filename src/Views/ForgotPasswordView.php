<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Views;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Util\Config;
use PHPLedger\Util\Html;
use PHPLedger\Util\L10n;

class ForgotPasswordView
{
    private ApplicationObjectInterface $app;

    private $pagetitle = "Recupera&ccedil;&atilde;o de palavra-passe";
    public function render(string $message = ""): void
    {
?>
        <!DOCTYPE html>
        <html lang="<?= $this->app->l10n()->html() ?>">

        <head>
            <title><?= Html::title($this->pagetitle) ?></title>
            <?php Html::header(); ?>
        </head>

        <body onload="document.getElementById('username').focus();">
            <?php
            ?>
            <div id="login">
                <h1><?= Config::instance()->get("title"); ?></h1>
                <p>Reposi&ccedil;&atilde;o de palavra-passe</p>
                <div class="main form config">
                    <p class='error'><?= $message; ?></p>
                    <form method="POST" name="forgot_password">
                        <p><label for="username">Utilizador</label><input id="username" size="50" maxlength="250" type="text" name="username" value="" required></p>
                        <p><label for="email">Endere&ccedil;o de email</label><input id="email" size="50" maxlength="250" type="text" name="email" value="" required></p>
                        <p style="text-align: center"><input type="submit" value="Repor"></p>
                    </form>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}
