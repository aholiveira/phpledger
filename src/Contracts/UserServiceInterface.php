<?php

/**
 * Interface for user services.
 *
 * Provides methods to access the current user and username within the application.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;

interface UserServiceInterface
{
    /**
     * Constructor accepting the main application object.
     *
     * @param ApplicationObjectInterface $app
     */
    public function __construct(ApplicationObjectInterface $app);

    /**
     * Get the currently authenticated user.
     *
     * @return UserObjectInterface|null
     */
    public function getCurrentUser(): ?UserObjectInterface;

    /**
     * Get the username of the currently authenticated user.
     *
     * @return string
     */
    public function getCurrentUsername(): string;
}
