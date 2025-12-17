<?php

namespace PHPLedger\Services;

use Exception;
use PHPLedger\Contracts\ConfigFilesystemInterface;
use PHPLedger\Contracts\ConfigurationServiceInterface;
use PHPLedger\Services\Logger;

class ConfigException extends Exception {}
class ConfigInvalidException extends Exception {}
class ConfigInvalidOrMissingException extends Exception {}
class ConfigUnsupportedException extends Exception {}

final class Config implements ConfigurationServiceInterface
{
    protected static array $configData = [];
    protected static string $validationMessage = "";
    private static string $file = '';
    private static ?ConfigurationServiceInterface $instance;
    private static ?ConfigFilesystemInterface $fs;

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
            $data = self::checkVersion($data, $test);
            if (!$test && !self::instance()->validate($data, $test)) {
                throw new ConfigException("Could not validate config data");
            }
            $configChanged = ($data !== $originalData);
            self::$configData = $data;
            if (!$test && $configChanged) {
                Logger::instance()->debug("Config has changed");
                self::save();
            }
            $status = true;
        } catch (ConfigException $e) {
            self::$validationMessage = $e->getMessage();
            $status = false;
        } catch (Exception $e) {
            Logger::instance()->error("Config init failed: " . $e->getMessage());
            $status = false;
        }
        return $status;
    }
    public static function instance(): ConfigurationServiceInterface
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private static function checkVersion(array $data, bool $test): array
    {
        $hasVersion = is_numeric($data['version'] ?? null);
        if (!$test && !$hasVersion) {
            Logger::instance()->debug("No version detected.");
        }
        return $data;
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
            Logger::instance()->debug("Error on JSON");
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
    /**
     * Sets a configuration value by its key.
     * @param string $key The configuration key.
     * @param mixed $value The configuration value.
     */
    public function set(string $key, $value, $save = true): void
    {
        if (empty(self::$file)) {
            throw new ConfigException("Config not initialized");
        }
        $parts = self::resolvePath($key);
        $ref = &self::$configData;
        $original = self::$configData; // store original for comparison
        foreach ($parts as $i => $p) {
            $last = $i === array_key_last($parts);
            if ($last) {
                $ref[$p] = $value;
            } else {
                if (!isset($ref[$p]) || !is_array($ref[$p])) {
                    $ref[$p] = [];
                }
                $ref = &$ref[$p];
            }
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
            Logger::instance()->error("Configuration file not set");
            throw new ConfigException("Configuration file not set");
        }
        if (!self::validate(self::$configData)) {
            Logger::instance()->error("Configuration data is not valid: " . self::$validationMessage);
            throw new ConfigException("Configuration data is not valid");
        }
        $dir = dirname(self::$file);
        if (!$fs->exists(self::$file)) {
            if (!$fs->isDir($dir)) {
                $fs->mkdir($dir);
            }
            if (!$fs->isWritable($dir)) {
                throw new ConfigException("Configuration directory is not writable");
            }
        }
        if ($fs->exists(self::$file) && !$fs->isWritable(self::$file)) {
            Logger::instance()->error("Configuration file is not writable: " . self::$file);
            throw new ConfigException("Configuration file is not writable");
        }
        $json = json_encode(self::$configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            Logger::instance()->error("Unable to encode configuration data to JSON: " . json_last_error_msg());
            throw new ConfigException("Unable to encode configuration data to JSON");
        }
        $tempFile = self::fs()->tempFile($dir);
        Logger::instance()->debug("Saving configuration to temporary file: $tempFile");
        Logger::instance()->dump($json);
        if ($fs->write($tempFile, $json) === false) {
            $fs->delete($tempFile);
            Logger::instance()->error("Unable to write configuration file: " . $tempFile);
            throw new ConfigException("Unable to save configuration file");
        }
        Logger::instance()->debug("Replacing configuration file: " . self::$file);
        if ($fs->exists(self::$file)) {
            $fs->delete(self::$file);
        }
        if (!$fs->replace($tempFile, self::$file)) {
            $fs->delete($tempFile);
            Logger::instance()->error("Unable to replace configuration file: " . self::$file);
            throw new ConfigException("Unable to replace configuration file");
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
            if (!$test && (!isset($cfg['version']) || !is_numeric($cfg['version']))) {
                throw new ConfigInvalidOrMissingException("'version'");
            }
            if (!isset($cfg['title']) || !is_string($cfg['title']) || trim($cfg['title']) === '') {
                throw new ConfigInvalidOrMissingException("'title'");
            }
            if (!isset($cfg['storage']) || !is_array($cfg['storage']) || !self::validateStorage($cfg['storage'] ?? [], $test)) {
                throw new ConfigInvalidOrMissingException("'storage'");
            }
            if (!isset($cfg['smtp']) || !is_array($cfg['smtp']) || !self::validateSmtpSettings($cfg['smtp'] ?? [], $test)) {
                throw new ConfigInvalidOrMissingException("'smtp'");
            }
            if (!isset($cfg['admin']) || !is_array($cfg['admin']) || !self::validateAdminSettings($cfg['admin'] ?? [], $test)) {
                throw new ConfigInvalidOrMissingException("'admin'");
            }
            $status = true;
        } catch (ConfigException $e) {
            self::$validationMessage = $e->getMessage();
            $status = false;
        } catch (ConfigUnsupportedException $e) {
            self::$validationMessage = "Unsupported {$e->getMessage()}";
            $status = false;
        } catch (ConfigInvalidException $e) {
            self::$validationMessage = "Invalid {$e->getMessage()}";
            $status = false;
        } catch (ConfigInvalidOrMissingException $e) {
            self::$validationMessage = "Invalid or missing {$e->getMessage()}";
            $status = false;
        }
        return $status;
    }
    /**
     * Validates the storage configuration.
     * @param array $storage The storage configuration to validate.
     * @return bool True if the storage configuration is valid, false otherwise.
     */
    private static function validateStorage(array $storage, bool $test = false): bool
    {
        if (!isset($storage['type'])) {
            throw new ConfigInvalidOrMissingException("'storage.type'");
        }
        if (!$test && $storage['type'] !== 'mysql') {
            throw new ConfigUnsupportedException("'storage.type'");
        }
        if (!isset($storage['settings']) || !is_array($storage['settings'])) {
            throw new ConfigInvalidOrMissingException("'storage.settings'");
        }
        if ($storage['type'] === 'mysql') {
            return self::validateMySqlStorage($storage['settings'] ?? [], $test);
        }
        return false;
    }
    /**
     * Validates MySQL storage settings.
     * @param array $settings The MySQL storage settings to validate.
     * @return bool True if the settings are valid, false otherwise.
     */
    private static function validateMySqlStorage(array $settings, $test = false): bool
    {
        if ($test) {
            return true;
        }
        foreach (['host', 'database', 'user'] as $k) {
            if (!isset($settings[$k]) || !is_string($settings[$k]) || trim($settings[$k]) === '') {
                throw new ConfigInvalidOrMissingException("'storage.settings.$k'");
            }
        }
        if (isset($settings['port']) && (!is_numeric($settings['port']) || (int)$settings['port'] === 0)) {
            throw new ConfigInvalidException("'storage.settings.port'");
        }
        return true;
    }
    /**
     * Validates SMTP settings.
     * @param array $settings The SMTP settings to validate.
     * @return bool True if the settings are valid, false otherwise.
     */
    private static function validateSmtpSettings(array $settings, $test = false): bool
    {
        if ($test) {
            return true;
        }
        if (!isset($settings['host']) || !is_string($settings['host']) || trim($settings['host']) === '') {
            throw new ConfigInvalidOrMissingException("'smtp.host'");
        }
        if (isset($settings['port']) && (!is_numeric($settings['port']) || (int)$settings['port'] === 0)) {
            throw new ConfigInvalidException("'smtp.port'");
        }
        if (
            !isset($settings['from']) ||
            !is_string($settings['from']) ||
            trim($settings['from']) === '' ||
            !filter_var($settings['from'], FILTER_VALIDATE_EMAIL)
        ) {
            throw new ConfigInvalidOrMissingException("'smtp.from'");
        }
        return true;
    }
    /**
     * Validates admin settings.
     * @param array $settings The admin settings to validate.
     * @return bool True if the settings are valid, false otherwise.
     */
    private static function validateAdminSettings(array $settings, $test = false): bool
    {
        if ($test) {
            return true;
        }
        if (empty($settings['username'])) {
            throw new ConfigInvalidOrMissingException("'admin.username'");
        }
        if (empty($settings['password'])) {
            throw new ConfigInvalidOrMissingException("'admin.password'");
        }
        return true;
    }
}
