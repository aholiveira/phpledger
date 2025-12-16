<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\TimezoneServiceInterface;

class TimezoneService implements TimezoneServiceInterface
{
    public function apply(string $default = "UTC"): string
    {
        $valid = timezone_identifiers_list();
        $default = in_array($default, $valid, true) ? $default : 'UTC';
        if (
            empty($_SESSION['timezone']) &&
            !empty($_COOKIE['timezone']) &&
            in_array($_COOKIE['timezone'], $valid, true)
        ) {
            $_SESSION['timezone'] = $_COOKIE['timezone'];
        }

        $tz = $_SESSION['timezone'] ?? $default;
        $toApply = in_array($tz, $valid, true) ? $tz : $default;
        date_default_timezone_set($toApply);
        return $toApply;
    }
}
