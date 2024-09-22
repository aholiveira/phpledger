<?php
class authentication
{
    static bool $_authenticated = false;
    public static function isAuthenticated(): bool
    {
        return static::$_authenticated;
    }

    public static function authenticate(string $username, string $password): bool
    {
        static::$_authenticated = static::do_internalAuth($username, $password);
        return static::$_authenticated;
    }

    private static function do_internalAuth(string $username, string $password): bool
    {
        global $object_factory;

        $host = config::get("host");
        $dbase = config::get("database");
        $config_user = config::get("user");
        $config_pass = config::get("password");
        $retval = false;
        $db_link = @mysqli_connect($host, $config_user, $config_pass, $dbase);
        if (!($db_link instanceof mysqli)) {
            /**
             * Config file credentials are invalid
             */
            print "Verifique o ficheiro config.json";
            exit(0);
        }
        /**
         * Config file credentials are valid.
         */
        $user_object = $object_factory->user();
        $user_object = $user_object->getByUsername($username);
        var_dump($user_object);
        if ($user_object instanceof user && strcasecmp($user_object->getUsername(), $username) == 0) {
            /**
             * User exists in internal auth DB
             * Authenticate user using data in the form
             */
            $retval = $user_object->verifyPassword($password);
        }
        return $retval;
    }
}
