<?php

namespace PHPLedger\Contracts\Domain;

use PHPLedger\Contracts\DataObjectInterface;

interface DefaultsObjectInterface extends DataObjectInterface
{
    public static function getByUsername(string $username): ?self;
    /**
     * Initialize Defaults
     * @return DefaultsObjectInterface
     */
    public static function init(): self;
}
