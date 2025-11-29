<?php
namespace PHPLedger\Util;

final class NumberUtil
{
    public static function normalize(?float $number): string
    {
        return null === $number ? "" : number_format($number, 2);
    }
}
