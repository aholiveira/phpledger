<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */
namespace PHPLedger\Contracts;

/**
 * Interface for configuration service operations.
 *
 * Provides methods to initialize, load, validate, and access
 * application configuration settings.
 */
interface ConfigurationServiceInterface
{
    /**
     * Initialize the configuration service with a config file.
     *
     * @param string $configfile Path to the configuration file
     * @param bool   $test       Whether to run in test mode
     * @return bool True on successful initialization
     */
    public static function init(string $configfile, bool $test = false): bool;

    /**
     * Load configuration data from a file.
     *
     * @param string $configfile Path to the configuration file
     * @param bool   $test       Whether to run in test mode
     * @return array Configuration data
     */
    public static function load(string $configfile, bool $test = false): array;

    /**
     * Check if the configuration has been loaded.
     *
     * @return bool
     */
    public static function loaded(): bool;

    /**
     * Set a configuration value.
     *
     * @param string $setting Configuration key
     * @param mixed  $value   Value to set
     */
    public function set(string $setting, mixed $value): void;

    /**
     * Get a configuration value.
     *
     * @param string $setting Configuration key
     * @param mixed  $default Default value if key is not set
     * @return mixed
     */
    public function get(string $setting, mixed $default = null): mixed;

    /**
     * Validate configuration data.
     *
     * @param array $data Configuration data to validate
     * @param bool  $test Whether to run in test mode
     * @return bool True if valid
     */
    public function validate(array $data, bool $test = false): bool;

    /**
     * Save the current configuration to its storage.
     */
    public function save(): void;

    /**
     * Get the current configuration as an array.
     *
     * @return array
     */
    public function getCurrent(): array;

    /**
     * Get the last validation message.
     *
     * @return string
     */
    public function getValidationMessage(): string;
}
