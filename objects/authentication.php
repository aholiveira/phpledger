<?php
class authentication
{
    public static bool $_authenticated = false;
    public static function isAuthenticated(): bool
    {
        return static::$_authenticated;
    }

    public static function authenticate(string $username, string $password): bool
    {
        global $object_factory;

        $user_object = $object_factory::user()->getByUsername($username);
        if ($user_object instanceof user) {
            static::$_authenticated = $user_object->verifyPassword($password);
        }
        return static::$_authenticated;
    }
}
