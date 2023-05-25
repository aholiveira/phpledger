<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

class Email
{
    static public function send_email($from, $to, $subject, $body): bool
    {
        strlen(config::get("smtp")) > 0 ? ini_set("smtp", config::get("smtp")) : "";
        strlen(config::get("smtp_port")) > 0 ? ini_set("smtp_port", config::get("smtp_port")) : "";
        strlen(config::get("from")) > 0 ?  ini_set("sendmail_from", config::get("from")) : "";
        strlen(config::get("smtp_port")) > 0 ?  ini_set("smtp_port", config::get("smtp_port")) : "";
        strlen($from) > 0 ? ini_set("sendmail_from", $from) : "";
        if (strlen($to) == 0 || strlen($subject) == 0 || strlen($body) == 0) return false;
        $from = ini_get("sendmail_from");
        $title = config::get("title");
        $headers["From"] = "\"{$title}\" <{$from}>";
        $headers["User-Agent"] = "PHP";
        $headers["Return-Path"] = $from;
        $headers["Content-Type"] = "text/plain; charset=us-ascii";
        $headers["X-Application"] = $title;
        return @mail($to, $subject, str_replace("\n.\n", "\n..\n", $body), $headers, "-f {$from}");
    }
}
