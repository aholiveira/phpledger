<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

final class NumberUtil
{
    public static function normalize(?float $number, int $decimals = 2): string
    {
        return null === $number ? "" : number_format($number, $decimals);
    }
}
