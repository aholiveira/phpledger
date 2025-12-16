<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Views\ResetPasswordView;
use PHPLedger\Views\Templates\ResetPasswordViewTemplate;

final class ResetPasswordController extends AbstractViewController
{
    private bool $success = false;
    private string $message = "";
    private ?string $tokenId = null;
    private string $refreshHeader = "index.php";
    private ?User $user = null;
    public function handle(): void
    {
        $this->getTokenId();
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $this->handleGet();
        }

        /* Validate token */
        if ($this->tokenId === null || empty($this->tokenId)) {
            $this->app->redirector()->to($this->refreshHeader, 8);
            $this->message = "Token em falta. Será redirecionado para a página inicial.";
        } else {
            $this->user = $this->app->dataFactory()::user()::getByToken($this->tokenId);
            if (!$this->user instanceof User || !$this->user->isTokenValid($this->tokenId)) {
                $this->app->redirector()->to($this->refreshHeader, 8);
                $this->message = "Token inválido ou expirado. Será redirecionado para a página inicial.";
            }
        }

        /* POST handler */
        if ($this->request->method() === "POST") {
            $this->handlePost();
        }
        $view = new ResetPasswordViewTemplate;
        $view->render(array_merge(
            $this->uiData,
            [
                'message' => $this->message,
                'success' => $this->success,
                'tokenId' => $this->tokenId,
                'apptitle' => $this->app->config()->get('title', ''),
            ]
        ));
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
        $filterArray = [
            "password" => FILTER_UNSAFE_RAW,
            "verifyPassword" => FILTER_UNSAFE_RAW,
            "tokenId" => FILTER_SANITIZE_ENCODED
        ];
        $filtered = filter_var_array($this->request->all(), $filterArray, true);
        $password = $filtered['password'] ?? '';
        $verifyPassword = $filtered['verifyPassword'];
        $tokenId = $filtered['tokenId'];
        if (empty($password) || empty($verifyPassword)) {
            $this->message = "Tem que indicar uma palavra-passe.";
        } elseif ($password !== $verifyPassword) {
            $this->message = "As palavras-passe não coincidem.";
        } else {
            if ($this->user instanceof User && $this->user->isTokenValid($tokenId)) {
                $this->user->setPassword($password);
                $this->user->setProperty('token', '');
                $this->user->setProperty('tokenExpiry', null);
                if ($this->user->update()) {
                    $this->app->redirector()->to($this->refreshHeader, 8);
                    $this->success = true;
                    $this->message = "Palavra-passe alterada com sucesso. Será redirecionado para a página inicial.";
                } else {
                    $this->message = "Erro ao atualizar a palavra-passe.";
                }
            } else {
                $this->app->redirector()->to($this->refreshHeader, 8);
                $this->message = "Token inválido ou expirado.";
            }
        }
    }
}
