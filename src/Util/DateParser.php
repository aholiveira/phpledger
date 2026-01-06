<?php

namespace PHPLedger\Util;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

class DateParser
{
    /**
     * Try to parse a full date string.
     *
     * @param string $key         e.g. 'data_mov'
     * @param array  $input       the filtered input array
     * @return DateTimeImmutable|null
     */
    public static function parseNamed(string $key, array $input): ?DateTimeImmutable
    {
        if (empty($input[$key])) {
            return null;
        }
        try {
            return new DateTimeImmutable($input[$key]);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid date format for {$key}");
        }
    }
}
