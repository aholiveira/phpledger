<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Services;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Contracts\UserServiceInterface;

final class UserService implements UserServiceInterface
{
    private ApplicationObjectInterface $app;

    public function __construct(ApplicationObjectInterface $app)
    {
        $this->app = $app;
    }

    public function getCurrentUser(): ?UserObjectInterface
    {
        $username = $this->app->session()->get('user', '');
        if (!$username) {
            return null;
        }
        $user = $this->app->dataFactory()->user()->getByUsername($username);
        return $user instanceof UserObjectInterface ? $user : null;
    }

    public function getCurrentUsername(): string
    {
        return $this->app->session()->get('user', '');
    }
}
