<?php

namespace PHPLedger\Contracts;

interface CsrfServiceInterface
{
    public function generateToken(): string;
    public function getToken(): ?string;
    public function validateToken(?string $token): bool;
    public function removeToken(): void;
    public function inputField(): string;
}
