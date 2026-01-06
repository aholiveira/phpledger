<?php

namespace PHPLedger\Contracts;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;

interface UserServiceInterface
{
    public function __construct(ApplicationObjectInterface $app);
    public function getCurrentUser(): ?UserObjectInterface;
    public function getCurrentUsername(): string;
}
