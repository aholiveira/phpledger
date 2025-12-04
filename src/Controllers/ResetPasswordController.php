<?php

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Views\ResetPasswordView;

final class ResetPasswordController
{
    private bool $success = false;
    private string $message = "";
    private ?string $tokenId = null;
    private string $refreshHeader = "Refresh: 8; URL=index.php";
    private ?User $user = null;

    public function handle(): void
    {
        $this->getTokenId();
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $this->handleGet();
        }

        /* Validate token */
        if ($this->tokenId === null || empty($this->tokenId)) {
            header($this->refreshHeader);
            $this->message = "Token em falta. Será redirecionado para a página inicial.";
        } else {
            $this->user = ObjectFactory::user()::getByToken($this->tokenId);
            if (!$this->user instanceof User || !$this->user->isTokenValid($this->tokenId)) {
                header($this->refreshHeader);
                $this->message = "Token inválido ou expirado. Será redirecionado para a página inicial.";
            }
        }

        /* POST handler */
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->handlePost();
        }
        $view = new ResetPasswordView;
        $view->render($this->tokenId, $this->success, $this->message);
    }
    private function getTokenId(): void
    {
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $this->tokenId = filter_input(INPUT_GET, "tokenId", FILTER_SANITIZE_ENCODED);
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->tokenId = filter_input(INPUT_POST, "tokenId", FILTER_SANITIZE_ENCODED);
        }
    }
    private function handleGet(): void
    {
        $this->getTokenId();
    }
    private function handlePost(): void
    {
        $password = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
        $verifyPassword = filter_input(INPUT_POST, "verifyPassword", FILTER_UNSAFE_RAW);
        $tokenId = filter_input(INPUT_POST, "tokenId", FILTER_SANITIZE_ENCODED);
        if (empty($password) || empty($verifyPassword)) {
            $this->message = "Tem que indicar uma palavra-passe.";
        } elseif ($password !== $verifyPassword) {
            $this->message = "As palavras-passe não coincidem.";
        } else {
            if ($this->user instanceof User && $this->user->isTokenValid($tokenId)) {
                $this->user->setPassword($password);
                $this->user->setToken('');
                $this->user->setTokenExpiry(null);
                if ($this->user->update()) {
                    header($this->refreshHeader);
                    $this->success = true;
                    $this->message = "Palavra-passe alterada com sucesso. Será redirecionado para a página inicial.";
                } else {
                    $this->message = "Erro ao atualizar a palavra-passe.";
                }
            } else {
                header($this->refreshHeader);
                $this->message = "Token inválido ou expirado.";
            }
        }
    }
}
