<?php

/**
 * Configuration class - Handles setting and getting configuration values
 *
 * @author Antonio Henrique Oliveira <aholiveira@gmail.com>
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 * 
 */
class Config
{
    protected static $_data = [];

    private function __construct()
    {
    }
    /**
     * Loads configuration from the configuration file
     *
     * @param string $configfile file to load configuration from
     * @return bool TRUE on success, FALSE on failure
     */
    public static function init(string $configfile): bool
    {
        try {
            if (file_exists($configfile)) {
                self::$_data = @json_decode(file_get_contents($configfile), true);
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Set a configuration value
     *
     * @param string $key the configuration key to store into
     * @param mixed $value the value to store in the configuration key
     * @return void
     */
    public static function set(string $key, $value): void
    {
        if (!is_array(self::$_data)) {
            self::$_data = [];
        }
        self::$_data[$key] = $value;
    }

    /**
     * Get a configuration value
     *
     * @param string $key the configuration key to get the value for
     * @return mixed the value stored in the corresponding key or NULL if the key does not exist
     */
    public static function get(string $key)
    {
        if (!is_array(self::$_data)) {
            self::$_data = [];
        }
        if (array_key_exists($key, self::$_data)) {
            return self::$_data[$key];
        } else {
            return null;
        }
    }
}
