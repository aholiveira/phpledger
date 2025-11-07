<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class Html
{
    /**
     * Option list for year selection
     * If no end value is provided current year is used
     * If no selected value is provided, current year is selected
     */
    public static function yearOptions(?int $selected = null, int $start = 1990, ?int $end = null): string
    {
        return self::buildOptions($start, null === $end ? date("Y") : $end, null === $selected ? date("Y") : $selected);
    }
    /**
     * Option list for month selection
     * If no end value is provided current month is used
     * If no selected value is provided, current month is selected
     */
    public static function monthOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 12, null === $selected ? date("n") : $selected);
    }
    /**
     * Option list for day selection
     * If no end value is provided current day is used
     * If no selected value is provided, current day is selected
     */
    public static function dayOptions(?string $selected = null): string
    {
        return self::buildOptions(1, 31, null === $selected ? date("d") : $selected);
    }
    public static function hourOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 23, null === $selected ? date("G") : $selected);
    }
    public static function minuteOptions(?string $selected = null): string
    {
        return self::buildOptions(0, 59, null === $selected ? date("i") : $selected);
    }
    public static function buildOptions(int $start, int $end, ?string $selected = null): string
    {
        $retval = "";
        $length = strlen($end);
        for ($i = $start; $i <= $end; $i++) {
            $retval .= sprintf("<option value=\"%d\" %s>%0{$length}d</option>\n", $i, ($i === (int) $selected ? "selected" : ""), $i);
        }
        return $retval;
    }
    public static function errortext(string $message): never
    {
        print "<p>{$message}</p>\n";
        print "</div>\n";
        print "</body>\n";
        print "</html>\n";
        die();
    }
    public static function myalert(string $message): void
    {
        print "<script type=\"text/javascript\" defer>\n";
        print "alert(" . json_encode($message) . ");\n";
        print "</script>";
    }
}
