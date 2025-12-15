<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Util;

use PHPLedger\Util\Config;

class Email
{
    public static function sendEmail($from, $to, $subject, $body, $test = false): bool
    {
        !empty(Config::instance()->get("smtp.host")) ? ini_set("smtp", Config::instance()->get("smtp.host")) : "";
        !empty(Config::instance()->get("smtp.port")) ? ini_set("smtp_port", Config::instance()->get("smtp.port")) : "";
        !empty(Config::instance()->get("smtp.from")) ? ini_set("sendmail_from", Config::instance()->get("smtp.from")) : "";
        !empty($from) ? ini_set("sendmail_from", $from) : "";
        if (empty($from) || empty($to) || empty($subject) || empty($body)) {
            return false;
        }
        $from = ini_get("sendmail_from");
        $title = Config::instance()->get("title");
        $headers["From"] = "\"{$title}\" <{$from}>";
        $headers["User-Agent"] = "PHP";
        $headers["Return-Path"] = $from;
        $headers["Content-Type"] = "text/plain; charset=us-ascii";
        $headers["X-Application"] = $title;

        if ($test) {
            return true;
        }
        return @mail($to, $subject, str_replace("\n.\n", "\n..\n", $body), $headers, "-f {$from}");
    }
}
