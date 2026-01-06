<?php

/**
 * Controller for handling password resets.
 *
 * Validates a token, allows the user to set a new password, and displays
 * success or error messages. Redirects to the homepage if token is invalid or missing.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Views\Templates\ResetPasswordViewTemplate;

final class ResetPasswordController extends AbstractViewController
{
    private bool $success = false;
    private string $message = "";
    private ?string $tokenId = null;
    private string $refreshHeader = "index.php";
    private ?User $user = null;

    protected function handle(): void
    {
        $this->getTokenId();

        if ($this->request->method() === "GET") {
            $this->handleGet();
        }

        if (empty($this->tokenId)) {
            $this->message = "Token em falta. Será redirecionado para a página inicial.";
            $this->app->redirector()->to($this->refreshHeader, 8);
        } else {
            $this->user = $this->app->dataFactory()::user()::getByToken($this->tokenId);
            if (!$this->user instanceof User || !$this->user->isTokenValid($this->tokenId)) {
                $this->message = "Token inválido ou expirado. Será redirecionado para a página inicial.";
                $this->app->redirector()->to($this->refreshHeader, 8);
            }
        }

        if ($this->request->method() === "POST") {
            $this->handlePost();
        }

        $view = new ResetPasswordViewTemplate();
        $view->render(array_merge($this->uiData, [
            'message'  => $this->message,
            'success'  => $this->success,
            'tokenId'  => $this->tokenId,
            'apptitle' => $this->app->config()->get('title', ''),
        ]));
    }

    private function getTokenId(): void
    {
        $this->tokenId = filter_var($this->request->input("tokenId"), FILTER_SANITIZE_ENCODED);
    }

    private function handleGet(): void
    {
        $this->getTokenId();
    }

    private function handlePost(): void
    {
        $filtered = filter_var_array($this->request->all(), [
            "password"       => FILTER_UNSAFE_RAW,
            "verifyPassword" => FILTER_UNSAFE_RAW,
            "tokenId"        => FILTER_SANITIZE_ENCODED
        ], true);

        $password = $filtered['password'] ?? '';
        $verifyPassword = $filtered['verifyPassword'] ?? '';
        $tokenId = $filtered['tokenId'] ?? '';

        if (empty($password) || empty($verifyPassword)) {
            $this->message = "Tem que indicar uma palavra-passe.";
        } elseif ($password !== $verifyPassword) {
            $this->message = "As palavras-passe não coincidem.";
        } elseif ($this->user instanceof User && $this->user->isTokenValid($tokenId)) {
            $this->user->setPassword($password);
            $this->user->setProperty('token', '');
            $this->user->setProperty('tokenExpiry', null);

            if ($this->user->update()) {
                $this->success = true;
                $this->message = "Palavra-passe alterada com sucesso. Será redirecionado para a página inicial.";
                $this->app->redirector()->to($this->refreshHeader, 8);
            } else {
                $this->message = "Erro ao atualizar a palavra-passe.";
            }
        } else {
            $this->message = "Token inválido ou expirado.";
            $this->app->redirector()->to($this->refreshHeader, 8);
        }
    }
}
