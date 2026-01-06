<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Contracts\PermissionServiceInterface;

final class PermissionService implements PermissionServiceInterface
{
    private int $role;

    public function __construct(UserObjectInterface $user)
    {
        $this->role = $user->getProperty('role', UserObjectInterface::USER_ROLE_RO); // default RO
    }

    public function canRead(): bool
    {
        return $this->role >= UserObjectInterface::USER_ROLE_RO; // all roles can read
    }

    public function canWrite(): bool
    {
        return $this->role >= UserObjectInterface::USER_ROLE_RW; // RW and Admin
    }

    public function isAdmin(): bool
    {
        return $this->role === UserObjectInterface::USER_ROLE_ADM;
    }
}
