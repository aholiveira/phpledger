<?php

namespace PHPLedger;

final class Version
{
    private const VERSION = '0.8.4';

    public static function string(): string
    {
        return self::VERSION;
    }

    public static function parts(): array
    {
        return explode('.', self::VERSION);
    }

    public static function major(): int
    {
        return (int) self::parts()[0];
    }

    public static function minor(): int
    {
        return (int) self::parts()[1];
    }

    public static function patch(): int
    {
        return (int) self::parts()[2];
    }

    public static function toInt(): int
    {
        [$major, $minor, $patch] = self::parts();
        return ($major * 1_000_000) + ($minor * 1_000) + $patch;
    }

    public static function isAtLeast(string $other): bool
    {
        return version_compare(self::VERSION, $other, '>=');
    }

    public static function equals(string $other): bool
    {
        return version_compare(self::VERSION, $other, '==');
    }
}
