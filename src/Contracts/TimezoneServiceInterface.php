<?php

/**
 * Interface for timezone services.
 *
 * Provides a method to apply and retrieve a timezone, with an optional default.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

interface TimezoneServiceInterface
{
    /**
     * Apply and retrieve the timezone.
     *
     * @param string $default Default timezone to use if none is set
     * @return string The applied timezone
     */
    public function apply(string $default = "UTC"): string;
}
