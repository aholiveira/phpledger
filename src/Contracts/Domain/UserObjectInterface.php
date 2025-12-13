<?php

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

interface UserObjectInterface extends DataObjectInterface
{
    public const USER_ROLE_ADM = 255;
    public const USER_ROLE_RW = 192;
    public const USER_ROLE_RO = 128;
    public function setProperty(string $name, mixed $value): void;
    public function getProperty(string $name, mixed $default = null): mixed;
    public function setPassword(string $value);
    public function getPassword(): ?string;
    public function verifyPassword(string $password): bool;
    public function createToken(): string;
    public function isTokenValid(string $token): bool;
    public function resetPassword(): bool;
    public function hasRole(int $role): bool;
    public static function getByUsername(string $username): ?UserObjectInterface;
    public static function getByToken(string $token): ?UserObjectInterface;
}
