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
    public static function year_option(?int $selected = null, int $start = 1990, ?int $end = null): string
    {
        return self::option_list($start, is_null($end) ? date("Y") : $end, is_null($selected) ? date("Y") : $selected);
    }
    /**
     * Option list for month selection
     * If no end value is provided current month is used
     * If no selected value is provided, current month is selected
     */
    public static function mon_option(?string $selected = null)
    {
        return self::option_list(1, 12, is_null($selected) ? date("n") : $selected);
    }
    /**
     * Option list for day selection
     * If no end value is provided current day is used
     * If no selected value is provided, current day is selected
     */
    public static function day_option(?string $selected = null)
    {
        return self::option_list(1, 31, is_null($selected) ? date("d") : $selected);
    }
    public static function hour_opt(?string $selected = null)
    {
        return self::option_list(0, 23, is_null($selected) ? date("G") : $selected);
    }
    public static function min_opt(?string $selected = null)
    {
        return self::option_list(0, 60, is_null($selected) ? date("i") : $selected);
    }
    public static function option_list(int $start, int $end, ?string $selected = null): string
    {
        $retval = "";
        $length = strlen($end);
        for ($i = $start; $i <= $end; $i++) {
            $retval .= sprintf("<option value=\"%d\" %s>%0{$length}d</option>\n", $i, ($i == $selected ? "selected" : ""), $i);
        }
        return $retval;
    }
    public static function errortext(string $message)
    {
        print "<p>{$message}</p>\n";
        print "</div>\n";
        print "</body>\n";
        print "</html>\n";
        die();
    }
    public static function myalert(string $message)
    {
        print "<script type=\"text/javascript\" defer>\n";
        print "alert(\"{$message}\");\n";
        print "</script>";
    }
}
