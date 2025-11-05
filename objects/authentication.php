<?php
class Authentication
{
    private static bool $authenticated = false;
    public static function isAuthenticated(): bool
    {
        return static::$authenticated;
    }

    public static function authenticate(string $username, string $password): bool
    {
        global $objectFactory;

        $user_object = $objectFactory::user()->getByUsername($username);
        if ($user_object instanceof user) {
            static::$authenticated = $user_object->verifyPassword($password);
        }
        return static::$authenticated;
    }
}
