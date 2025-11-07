<?php
namespace PHPLedger\Util;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
class DateParser
{
    /**
     * Try to parse a full date string or its AA/MM/DD parts.
     *
     * @param string $key         e.g. 'data_mov'
     * @param array  $input       the filtered input array
     * @return DateTimeImmutable|null
     */
    public static function parseNamed(string $key, array $input): ?DateTimeImmutable
    {
        // 1) If the raw field is non-empty, trust PHP to parse it:
        if (!empty($input[$key])) {
            try {
                return new DateTimeImmutable($input[$key]);
            } catch (Exception $e) {
                throw new InvalidArgumentException("Invalid date format for {$key}");
            }
        }

        // 2) Otherwise, pull apart AA/MM/DD
        $parts = ['AA' => 0, 'MM' => 0, 'DD' => 0];
        foreach (array_keys($parts) as $suffix) {
            $field = "{$key}{$suffix}";
            if (isset($input[$field]) && $input[$field] !== '') {
                $parts[$suffix] = (int) $input[$field];
            }
        }

        // If none of the parts were provided, bail
        if (empty($parts['AA']) && empty($parts['MM']) && empty($parts['DD'])) {
            return null;
        }

        // Validate calendar date (month, day, year)
        if (!checkdate($parts['MM'], $parts['DD'], $parts['AA'])) {
            throw new InvalidArgumentException("Invalid date parts for {$key}");
        }

        // Build a proper YYYY-MM-DD string
        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $parts['AA'], $parts['MM'], $parts['DD']));
    }
}
