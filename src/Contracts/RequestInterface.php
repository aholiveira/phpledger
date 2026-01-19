<?php

/**
 * Interface for HTTP request handling.
 *
 * Provides methods to retrieve the request method, access input data,
 * and get all request parameters.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface RequestInterface
{
    /**
     * Get the HTTP request method (e.g., GET, POST).
     *
     * @return string
     */
    public function method(): string;

    /**
     * Returns true if HTTP request is a POST
     *
     * @return array
     */
    public function isPost(): bool;

    /**
     * Returns true if HTTP request is a GET
     *
     * @return array
     */
    public function isGet(): bool;

    /**
     * Retrieve a specific input value from the request.
     *
     * @param string $key Input key
     * @param mixed $default Default value if key is not present
     * @return mixed
     */
    public function input(string $key, mixed $default = null);

    /**
     * Get all input data from the request.
     *
     * @return array
     */
    public function all(): array;
}
