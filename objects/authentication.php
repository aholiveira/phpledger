<?php
class authentication
{

    public static function isAuthenticated(): bool
    {
        return true;
    }
    public static function authenticate(string $username, string $password): bool
    {
        if (static::isAuthOnConfig()) {
            return static::do_internalAuth($username, $password);
        } else {
            return static::do_mysqlAuth($username, $password);
        }
        return true;
    }
    public static function isAuthOnConfig(): bool
    {
        return (!empty(config::get("user")) && !empty(config::get("password")));
    }

    private static function do_mysqlAuth(string $username, string $password): bool
    {
        /**
         * Do MySQL auth
         */
        $password_hash = md5($password);
        $_SESSION['user'] = $username;
        $_SESSION['pass'] = $password_hash;
        config::set("user", $username);
        config::set("password", $password_hash);
        $db_link = @mysqli_connect(config::get("host"), $username, $password_hash, config::get("database"));
        return ($db_link instanceof \mysqli);
    }

    private static function do_internalAuth(string $username, string $password): bool
    {
        global $host;
        global $dbase;
        global $db_link;
        global $object_factory;

        $username = config::get("user");
        $pass = config::get("password");
        $retval = false;
        $db_link = @mysqli_connect($host, $username, $pass, $dbase);
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
        $user_object->getByUsername($_POST["userid"]);
        if (strcasecmp($user_object->getUsername(), $_POST["userid"]) == 0) {
            /**
             * User exists in internal auth DB
             * Authenticate user using data in the form
             */
            $retval = $user_object->verifyPassword($_POST["pass"]);
        } else {
            /**
             * User does not exist in internal DB.
             */
            $retval = verify_and_migrate_user();
        }
        return $retval;
    }
}
