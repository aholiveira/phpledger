<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Services;

use Exception;
use PHPLedger\Contracts\ConfigFilesystemInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Exceptions\ConfigException;
use Throwable;

final class Config implements ConfigurationServiceInterface
{
    protected static array $configData = [];
    protected static string $validationMessage = "";
    private static string $file = '';
    private static ?ConfigurationServiceInterface $instance;
    private static ?ConfigFilesystemInterface $fs;
    private static bool $loaded = false;

    public static function reset(): void
    {
        self::$configData = [];
        self::$validationMessage = '';
        self::$file = '';
        self::$instance = null;
        self::$fs = null;
    }
    private static function fs(): ConfigFilesystemInterface
    {
        if (!isset(self::$fs)) {
            self::$fs = new NativeFilesystem();
        }
        return self::$fs;
    }

    public static function setFilesystem(ConfigFilesystemInterface $fs): void
    {
        self::$fs = $fs;
    }

    public static function setInstance(self $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Initializes the configuration by loading it from the specified file.
     * @param string $configfile The path to the configuration file.
     * @param bool $test If true, skips migration for testing purposes.
     * @return bool True if initialization was successful, false otherwise.
     * @throws Exception if there is an error reading or parsing the configuration file.
     */
    public static function init(string $configfile, bool $test = false): bool
    {
        try {
            if ($test) {
                self::$file = $configfile;
            }
            $data = self::load($configfile);
            $originalData = $data ?? [];
            if (!$test && !self::instance()->validate($data, $test)) {
                throw new ConfigException("Could not validate config data");
            }
            $configChanged = ($data !== $originalData);
            self::$configData = $data;
            if (!$test && $configChanged) {
                self::save();
            }
            $status = true;
        } catch (ConfigException $e) {
            self::$validationMessage = $e->getMessage();
            $status = false;
        } catch (Throwable $e) {
            $status = false;
        }
        self::$loaded = $status;
        return $status;
    }

    public static function loaded(): bool
    {
        return self::$loaded;
    }

    public static function instance(): ConfigurationServiceInterface
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public static function load(string $configfile, bool $test = false): array
    {
        $data = null;
        if (!self::fs()->exists($configfile)) {
            self::$file = $configfile;
            throw new ConfigException("Config file does not exists");
        }
        $raw = self::fs()->read($configfile);
        if ($raw === false) {
            throw new ConfigException("Invalid config data");
        }
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::$file = $configfile;
        if ($data === null || !is_array($data)) {
            throw new ConfigException("Error on JSON");
        }
        return $data;
    }
    public function getValidationMessage(): string
    {
        return self::$validationMessage;
    }
    /**
     * Gets the current configuration data.
     * @return array The current configuration data.
     */
    public function getCurrent(): array
    {
        return self::$configData;
    }
    /**
     * Resolves a dot-separated configuration path into an array of keys.
     * @param string $path The dot-separated configuration path.
     * @return array The array of keys.
     */
    private static function resolvePath(string $path): array
    {
        return explode('.', $path);
    }

    private static function &resolveOrCreate(array &$data, array $parts): mixed
    {
        $ref = &$data;
        foreach ($parts as $p) {
            if (!isset($ref[$p]) || !is_array($ref[$p])) {
                $ref[$p] = [];
            }
            $ref = &$ref[$p];
        }
        return $ref;
    }

    /**
     * Sets a configuration value by its key.
     * @param string $key The configuration key.
     * @param mixed $value The configuration value.
     */
    public function set(string $key, $value, bool $save = true): void
    {
        if (empty(self::$file)) {
            throw new ConfigException("Config not initialized");
        }

        $parts = self::resolvePath($key);
        $original = self::$configData;

        // Resolve reference to the last part
        $lastKey = array_pop($parts);
        $ref = &self::resolveOrCreate(self::$configData, $parts);

        // Merge if both are arrays, otherwise assign
        if (is_array($value) && isset($ref[$lastKey]) && is_array($ref[$lastKey])) {
            $ref[$lastKey] = array_replace_recursive($ref[$lastKey], $value);
        } else {
            $ref[$lastKey] = $value;
        }

        if ($save && self::$configData !== $original) {
            self::save();
        }
    }

    /**
     * Retrieves a configuration value by its key.
     * @param string $key The configuration key.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The configuration value, or the default value if the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $parts = self::resolvePath($key);
        $ref = self::$configData;
        foreach ($parts as $p) {
            if (!is_array($ref) || !array_key_exists($p, $ref)) {
                return $default;
            }
            $ref = $ref[$p];
        }
        return $ref;
    }
    /**
     * Saves the current configuration data to the configuration file.
     * @throws Exception if there is an error saving the configuration file.
     */
    public function save(): void
    {
        $fs = self::fs();
        if (empty(self::$file)) {
            throw new ConfigException("Configuration file not set", ConfigException::INVALID);
        }
        if (!self::validate(self::$configData)) {
            throw new ConfigException("Configuration data is not valid", ConfigException::INVALID);
        }
        $dir = dirname(self::$file);
        if (!$fs->exists(self::$file)) {
            if (!$fs->isDir($dir)) {
                $fs->mkdir($dir);
            }
            if (!$fs->isWritable($dir)) {
                throw new ConfigException("Configuration directory is not writable", ConfigException::INVALID);
            }
        }
        if ($fs->exists(self::$file) && !$fs->isWritable(self::$file)) {
            throw new ConfigException("Configuration file is not writable", ConfigException::INVALID);
        }
        $json = json_encode(self::$configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new ConfigException("Unable to encode configuration data to JSON", ConfigException::INVALID);
        }
        $tempFile = self::fs()->tempFile($dir);
        if ($fs->write($tempFile, $json) === false) {
            $fs->delete($tempFile);
            throw new ConfigException("Unable to save configuration file", ConfigException::INVALID);
        }
        if ($fs->exists(self::$file)) {
            $fs->delete(self::$file);
        }
        if (!$fs->replace($tempFile, self::$file)) {
            $fs->delete($tempFile);
            throw new ConfigException("Unable to replace configuration file", ConfigException::INVALID);
        }
    }
    /**
     * Validates the configuration data.
     * @param array $cfg The configuration data to validate.
     * @return bool True if the configuration data is valid, false otherwise.
     */
    public function validate(array $cfg, bool $test = false): bool
    {
        try {
            $validator = new ConfigValidator($cfg);
            $status = $validator->validate($test);
            self::$validationMessage = $validator->getValidationMessage();
            return $status;
        } catch (ConfigException $e) {
            self::$validationMessage = $e->getMessage();
            return false;
        }
    }
}
