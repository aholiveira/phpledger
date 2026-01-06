<?php

/**
 * Interface for session management services.
 *
 * Provides methods to start sessions, check authentication and expiration,
 * manage session data, and handle logout and session refresh.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface SessionServiceInterface
{
    /**
     * Start the session.
     */
    public function start(): void;

    /**
     * Check if the session is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Check if the session has expired.
     *
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Log out the current session.
     */
    public function logout(): void;

    /**
     * Refresh the session expiration time.
     *
     * @param int $ttl Time-to-live in seconds
     */
    public function refreshExpiration(int $ttl = 3600): void;

    /**
     * Set a session value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void;

    /**
     * Get a session value.
     *
     * @param string $key
     * @param mixed $default Default value if key is not present
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;
}
