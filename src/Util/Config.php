<?php

namespace PHPLedger\Util;

use Exception;

final class Config
{
    protected static array $configData = [];
    protected static string $validationMessage;
    private static string $file = '';

    private function __construct() {}
    /**
     * Initializes the configuration by loading it from the specified file.
     * @param string $configfile The path to the configuration file.
     * @param bool $test If true, skips migration for testing purposes.
     * @return bool True if initialization was successful, false otherwise.
     * @throws Exception if there is an error reading or parsing the configuration file.
     */
    public static function init(string $configfile, $test = false): bool
    {
        try {
            if (!file_exists($configfile)) {
                return false;
            }
            self::$file = $configfile;
            $raw = file_get_contents(self::$file);
            if ($raw === false) {
                return false;
            }
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if ($data === null || !is_array($data)) {
                return false;
            }
            $hasVersion = is_numeric($data['version'] ?? null);
            if (!$test && !$hasVersion) {
                $data = ConfigMigrator::migrate($data);
            } elseif (!$hasVersion) {
                return false;
            }
            if (!$test && !self::validate($data)) {
                return false;
            }
            self::$configData = $data;
            if (!$test) {
                self::save();
            }
            return true;
        } catch (Exception $e) {
            Logger::instance()->error("Config init failed: " . $e->getMessage());
            return false;
        }
    }
    public static function getValidationMessage(): string
    {
        return self::$validationMessage;
    }
    /**
     * Gets the current configuration data.
     * @return array The current configuration data.
     */
    public static function getCurrent(): array
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
    public static function set(string $key, $value, $save = true): void
    {
        $parts = self::resolvePath($key);
        $ref = &self::$configData;
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
        if ($save) {
            self::save();
        }
    }
    /**
     * Retrieves a configuration value by its key.
     * @param string $key The configuration key.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The configuration value, or the default value if the key does not exist.
     */
    public static function get(string $key, mixed $default = null): mixed
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
    public static function save(): void
    {
        if (!self::$file) {
            Logger::instance()->error("Configuration file not set");
            throw new Exception("Configuration file not set");
        }
        if (!self::validate(self::$configData)) {
            Logger::instance()->error("Configuration data is not valid: " . self::$validationMessage);
            throw new Exception("Configuration data is not valid");
        }
        $dir = dirname(self::$file);
        if (!file_exists(self::$file) && (!is_dir($dir) || !is_writable($dir))) {
            Logger::instance()->error("Configuration directory is not writable: " . $dir);
            throw new Exception("Configuration directory is not writable");
        }
        if (file_exists(self::$file) && !is_writable(self::$file)) {
            Logger::instance()->error("Configuration file is not writable: " . self::$file);
            throw new Exception("Configuration file is not writable");
        }
        $json = json_encode(self::$configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            Logger::instance()->error("Unable to encode configuration data to JSON: " . json_last_error_msg());
            throw new Exception("Unable to encode configuration data to JSON");
        }
        $tempFile = tempnam($dir, 'cfg_');
        Logger::instance()->debug("Saving configuration to temporary file: $tempFile");
        Logger::instance()->dump($json);
        if (file_put_contents($tempFile, $json, LOCK_EX) === false) {
            #@unlink($tempFile);
            Logger::instance()->error("Unable to write configuration file: " . $tempFile);
            throw new Exception("Unable to save configuration file");
        }
        Logger::instance()->debug("Replacing configuration file: " . self::$file);
        if (!rename($tempFile, self::$file)) {
            #@unlink($tempFile);
            Logger::instance()->error("Unable to replace configuration file: " . self::$file);
            throw new Exception("Unable to replace configuration file");
        }
    }
    /**
     * Validates the configuration data.
     * @param array $cfg The configuration data to validate.
     * @return bool True if the configuration data is valid, false otherwise.
     */
    public static function validate(array $cfg, $test = false): bool
    {
        if (!$test && (!isset($cfg['version']) || !is_numeric($cfg['version']))) {
            self::$validationMessage = "Invalid or missing 'version'";
            return false;
        }
        if (!isset($cfg['title']) || !is_string($cfg['title']) || trim($cfg['title']) === '') {
            self::$validationMessage = "Invalid or missing 'title'";
            return false;
        }
        if (!isset($cfg['storage']) || !is_array($cfg['storage']) || !self::validateStorage($cfg['storage'] ?? [], $test)) {
            self::$validationMessage = "Invalid or missing 'storage'";
            return false;
        }
        if (!isset($cfg['smtp']) || !is_array($cfg['smtp']) || !self::validateSmtpSettings($cfg['smtp'] ?? [], $test)) {
            self::$validationMessage = "Invalid or missing 'smtp'";
            return false;
        }
        if (!isset($cfg['admin']) || !is_array($cfg['admin']) || !self::validateAdminSettings($cfg['admin'] ?? [], $test)) {
            self::$validationMessage = "Invalid or missing 'admin'";
            return false;
        }
        return true;
    }
    /**
     * Validates the storage configuration.
     * @param array $storage The storage configuration to validate.
     * @return bool True if the storage configuration is valid, false otherwise.
     */
    private static function validateStorage(array $storage, $test = false): bool
    {
        if (!isset($storage['type'])) {
            self::$validationMessage = "Invalid or missing 'storage.type'";
            return false;
        }
        if (!$test && $storage['type'] !== 'mysql') {
            self::$validationMessage = "Unsupported 'storage.type'";
            return false;
        }
        if (!isset($storage['settings']) || !is_array($storage['settings'])) {
            self::$validationMessage = "Invalid or missing 'storage.settings'";
            return false;
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
                self::$validationMessage = "Invalid or missing 'storage.settings.$k'";
                return false;
            }
        }
        if (isset($settings['port']) && !is_numeric($settings['port'])) {
            self::$validationMessage = "Invalid 'storage.settings.port'";
            return false;
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
            self::$validationMessage = "Invalid or missing 'smtp.host'";
            return false;
        }
        if (isset($settings['port']) && !is_numeric($settings['port'])) {
            self::$validationMessage = "Invalid 'smtp.port'";
            return false;
        }
        if (
            !isset($settings['from']) ||
            !is_string($settings['from']) ||
            trim($settings['from']) === '' ||
            !filter_var($settings['from'], FILTER_VALIDATE_EMAIL)
        ) {
            self::$validationMessage = "Invalid or missing 'smtp.from'";
            return false;
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
        if (empty($settings['username']) || empty($settings['password'])) {
            self::$validationMessage = "Invalid or missing 'admin.username' or 'admin.password'";
            return false;
        }
        return true;
    }
}
