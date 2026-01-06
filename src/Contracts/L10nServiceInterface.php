<?php

/**
 * Interface for localization (L10n) services.
 *
 * Provides methods for translating strings, managing language settings,
 * generating HTML representations, and sanitizing language codes.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface L10nServiceInterface
{
    /**
     * Get the HTML representation for the current localization.
     *
     * @return string
     */
    public function html(): string;

    /**
     * Translate a string by its translation ID with optional replacements.
     *
     * @param string $translationId
     * @param mixed  ...$replacements
     * @return string
     */
    public function l(string $translationId, mixed ...$replacements): string;

    /**
     * Get the current language code.
     *
     * @return string
     */
    public function lang(): string;

    /**
     * Print or output a translated string.
     *
     * @param string $translationId
     * @param mixed  ...$replacements
     */
    public function pl(string $translationId, mixed ...$replacements): void;

    /**
     * Sanitize a language code.
     *
     * @param string|null $lang
     * @return string
     */
    public function sanitizeLang(?string $lang): string;

    /**
     * Set the current language.
     *
     * @param string $lang
     */
    public function setLang(string $lang): void;
}
