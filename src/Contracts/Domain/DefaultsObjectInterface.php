<?php

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

interface DefaultsObjectInterface extends DataObjectInterface
{
    public static function getByUsername(string $username): ?DefaultsObjectInterface;
    /**
     * Initialize Defaults
     * @return DefaultsObjectInterface
     */
    public static function init(): DefaultsObjectInterface;
}
