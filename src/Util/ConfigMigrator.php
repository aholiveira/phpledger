<?php

namespace PHPLedger\Util;

class ConfigMigrator
{
    public static function migrate(array $oldConfig): array
    {
        if (($oldConfig['version'] ?? null) === 2) {
            return $oldConfig;
        }
        if (isset($oldConfig['storage'], $oldConfig['smtp'], $oldConfig['admin'])) {
            return $oldConfig;
        }
        $newConfig = [];

        // Update title
        $newConfig['title'] = $oldConfig['title'] ?? 'Gestão financeira';

        // Migrate backend to storage
        $newConfig['storage'] = self::migrateMySQL($oldConfig);

        // Migrate SMTP settings
        $newConfig['smtp'] = self::migrateMail($oldConfig);

        // Admin credentials
        $newConfig['admin'] = self::migrateAdmin($oldConfig);

        // URL
        $newConfig['url'] = $oldConfig['url'] ?? 'https://yourhostname/contas';

        // Add version
        $newConfig['version'] = 2;

        // Return the new configuration array
        return $newConfig;
    }
    private static function migrateMySQL(array $oldConfig): array
    {
        return [
            'type' => $oldConfig['backend'] ?? 'mysql',
            'settings' => [
                'host' => $oldConfig['host'] ?? 'localhost',
                'database' => $oldConfig['database'] ?? 'contas',
                'user' => $oldConfig['user'] ?? 'your-username',
                'password' => $oldConfig['password'] ?? 'your-password',
                'port' => $oldConfig['port'] ?? 3306,
                'ssl' => false,
            ]
        ];
    }

    private static function migrateMail(array $oldConfig): array
    {
        if (isset($oldConfig['smtp']) && is_array($oldConfig['smtp'])) {
            return ['smtp' => $oldConfig['smtp']];
        }
        return [
            'host' => $oldConfig['smtp'] ?? 'localhost',
            'port' => $oldConfig['smtp_port'] ?? 25,
            'from' => $oldConfig['from'] ?? 'youremailhere@example.com'
        ];
    }
    private static function migrateAdmin(array $oldConfig): array
    {
        return [
            'username' => $oldConfig['admin_username'] ?? 'admin',
            'password' => $oldConfig['admin_password'] ?? 'admin'
        ];
    }
}
