<?php

/**
 * Interface for permission checking services.
 *
 * Provides methods to determine a user's access rights, including
 * read, write, and administrative permissions.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

use PHPLedger\Domain\User;

interface PermissionServiceInterface
{
    /**
     * Constructor accepting a User object.
     *
     * @param User $user The user whose permissions will be checked
     */
    public function __construct(User $user);

    /**
     * Check if the user has read access.
     *
     * @return bool
     */
    public function canRead(): bool;

    /**
     * Check if the user has write access.
     *
     * @return bool
     */
    public function canWrite(): bool;

    /**
     * Check if the user has administrative privileges.
     *
     * @return bool
     */
    public function isAdmin(): bool;
}
