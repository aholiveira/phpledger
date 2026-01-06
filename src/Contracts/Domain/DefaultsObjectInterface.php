<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

/**
 * Interface for default domain objects.
 *
 * Provides methods to retrieve and initialize default objects
 * based on username or project-specific defaults.
 */
interface DefaultsObjectInterface extends DataObjectInterface
{
    /**
     * Retrieve a Defaults object by username.
     *
     * @param string $username The username to fetch defaults for
     * @return self|null The Defaults object or null if not found
     */
    public static function getByUsername(string $username): ?self;

    /**
     * Initialize a Defaults object with default values.
     *
     * @return self Initialized Defaults object
     */
    public static function init(): self;
}
