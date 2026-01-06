<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

/**
 * Interface for user domain objects.
 *
 * Defines properties, authentication, and role management methods
 * for user objects within the system.
 */
interface UserObjectInterface extends DataObjectInterface
{
    public const USER_ROLE_ADM = 255;
    public const USER_ROLE_RW  = 192;
    public const USER_ROLE_RO  = 128;

    /**
     * Set a property value on the user object.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     */
    public function setProperty(string $name, mixed $value): void;

    /**
     * Get a property value from the user object.
     *
     * @param string $name    Property name
     * @param mixed  $default Default value if property is not set
     * @return mixed
     */
    public function getProperty(string $name, mixed $default = null): mixed;

    /**
     * Set the user's password.
     *
     * @param string $value Plaintext password
     */
    public function setPassword(string $value);

    /**
     * Get the user's hashed password.
     *
     * @return string|null
     */
    public function getPassword(): ?string;

    /**
     * Verify a plaintext password against the stored password.
     *
     * @param string $password Plaintext password
     * @return bool True if valid, false otherwise
     */
    public function verifyPassword(string $password): bool;

    /**
     * Create a new authentication token for the user.
     *
     * @return string The token
     */
    public function createToken(): string;

    /**
     * Check if a token is still valid.
     *
     * @param string $token Token to validate
     * @return bool
     */
    public function isTokenValid(string $token): bool;

    /**
     * Reset the user's password.
     *
     * @return bool True if successful
     */
    public function resetPassword(): bool;

    /**
     * Check if the user has a specific role.
     *
     * @param int $role Role to check
     * @return bool
     */
    public function hasRole(int $role): bool;

    /**
     * Retrieve a user by username.
     *
     * @param string $username
     * @return self|null
     */
    public static function getByUsername(string $username): ?self;

    /**
     * Retrieve a user by authentication token.
     *
     * @param string $token
     * @return self|null
     */
    public static function getByToken(string $token): ?self;
}
