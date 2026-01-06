<?php

/**
 * Interface for view controllers.
 *
 * Defines a method to handle HTTP requests and interact with the application.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface ViewControllerInterface
{
    /**
     * Handle an incoming request using the application context.
     *
     * @param ApplicationObjectInterface $app
     * @param RequestInterface $request
     */
    public function handleRequest(ApplicationObjectInterface $app, RequestInterface $request): void;
}
