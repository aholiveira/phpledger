<?php

namespace PHPLedger\Contracts;

use PHPLedger\Domain\User;

interface PermissionServiceInterface
{
    public function __construct(User $user);
    public function canRead(): bool;
    public function canWrite(): bool;
    public function isAdmin(): bool;
}
