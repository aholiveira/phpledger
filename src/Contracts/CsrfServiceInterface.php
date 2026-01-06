<?php

/**
 * Interface for CSRF (Cross-Site Request Forgery) protection service.
 *
 * Provides methods to generate, validate, retrieve, and remove CSRF tokens,
 * as well as generate HTML input fields for forms.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface CsrfServiceInterface
{
    /**
     * Generate a new CSRF token.
     *
     * @return string The generated token
     */
    public function generateToken(): string;

    /**
     * Get the current CSRF token.
     *
     * @return string|null The token, or null if none exists
     */
    public function getToken(): ?string;

    /**
     * Validate a given CSRF token.
     *
     * @param string|null $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public function validateToken(?string $token): bool;

    /**
     * Remove the current CSRF token.
     */
    public function removeToken(): void;

    /**
     * Generate an HTML input field containing the CSRF token.
     *
     * @return string HTML input field
     */
    public function inputField(): string;
}
