<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\ForgotPasswordView;

final class ForgotPasswordController
{
    public function handle(): void
    {
        $filterArray = [
            "username" => FILTER_SANITIZE_ENCODED,
            "email" => FILTER_SANITIZE_ENCODED
        ];
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $filtered = filter_input_array(INPUT_POST, $filterArray, true);
            if (empty($filtered["username"]) || empty($filtered["email"])) {
                $message = "Indique o username e o email registados na aplica&ccedil;&atilde;o";
            }
            $user = ObjectFactory::user()::getByUsername($filtered["username"]);
            if (!($user instanceof User)) {
                $message = "Os dados indicados est&atilde;o errados.";
            }
            if ($user !== null && strtolower($user->getEmail()) === $filtered["email"]) {
                $message = $user->resetPassword() ?
                    "<p>Ir&aacute; receber um email com um link para efectuar a reposicao da palavra-passe.<br></p>"
                    :
                    "Falhou a criacao do token de reposicao ou o envio do email. Verifique as configuracoes ou os dados fornecidos e tente novamente.";
            }
        }
        $view = new ForgotPasswordView;
        $view->render(isset($message) ? $message : "");
    }
}
