<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

use PHPLedger\Services\Config;

final class Html
{
    public static function yearOptions(?int $selected = null, int $start = 1990, ?int $end = null): string
    {
        return self::buildOptions($start, $end ?? date("Y"), $selected ?? (int) date("Y"));
    }
    public static function monthOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 12, $selected ?? date("n"));
    }
    public static function dayOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 31, $selected ?? date("d"));
    }
    public static function hourOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 23, $selected ?? date("G"));
    }
    public static function minuteOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 59, $selected ?? date("i"));
    }
    public static function buildOptions(int $start, int $end, ?string $selected = null): string
    {
        $selectedValue = (int) $selected;
        $retval = "";
        $length = \strlen((string) $end);
        for ($i = $start; $i <= $end; $i++) {
            $s = $i === $selectedValue ? ' selected' : '';
            $retval .= \sprintf("<option value=\"%d\"%s>%0{$length}d</option>\n", $i, $s, $i);
        }
        return $retval;
    }
    public static function header(): void
    {
?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script defer src="assets/js/set-timezone.js"></script>
        <link rel="stylesheet" href="assets/styles.css">
        <link rel="icon" type="image/x-icon" href="assets/media/logo-2.ico">
        <link rel="shortcut icon" type="image/x-icon" href="assets/media/logo-2.ico">
<?php
    }
    public static function title(string $pagetitle = "", string $appTitle = "Prosperidade financeira")
    {
        $title = trim($pagetitle) !== '' ? "$pagetitle - " : '';
        $fullTitle = $title . $appTitle;
        return htmlspecialchars($fullTitle);
    }
}
