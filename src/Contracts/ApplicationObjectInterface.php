<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

use PHPLedger\Services\FileResponseSender;
use PHPLedger\Storage\ReportFactory;

/**
 * Interface for the main application object.
 *
 * Provides access to core services such as configuration, logging,
 * session management, reporting, and error handling within the application.
 */
interface ApplicationObjectInterface
{
    public function config(): ConfigurationServiceInterface;
    public function dataFactory(): DataObjectFactoryInterface;
    public function reportFactory(): ReportFactory;
    public function l10n(): L10nServiceInterface;
    public function logger(): LoggerServiceInterface;
    public function session(): SessionServiceInterface;
    public function redirector(): RedirectorServiceInterface;
    public function csrf(): CsrfServiceInterface;
    public function headerSender(): HeaderSenderInterface;
    public function fileResponseSender(): FileResponseSender;

    /**
     * Set a global error message.
     *
     * @param string $message
     */
    public function setErrorMessage(string $message): void;

    /**
     * Clear any previously set error message.
     */
    public function clearErrorMessage(): void;

    /**
     * Get the current error message.
     *
     * @return string
     */
    public function getErrorMessage(): string;

    /**
     * Check whether the application is installed.
     *
     * @return bool
     */
    public function isInstalled(): bool;
}
