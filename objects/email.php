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
        global $config;
        if (strlen($config->getParameter("smtp")) > 0) ini_set("smtp", $config->getParameter("smtp"));
        if (strlen($config->getParameter("smtp_port")) > 0) ini_set("smtp_port", $config->getParameter("smtp_port"));
        if (strlen($config->getParameter("from")) > 0) ini_set("sendmail_from", $config->getParameter("from"));
        if (strlen($config->getParameter("smtp_port")) > 0) ini_set("smtp_port", $config->getParameter("smtp_port"));
        if (strlen($from) > 0) ini_set("sendmail_from", $from);
        if (strlen($to) == 0 || strlen($subject) == 0 || strlen($body) == 0) return false;
        $from = ini_get("sendmail_from");
        $headers["From"] = "\"Gestao financeira\" <{$from}>";
        $headers["User-Agent"] = "PHP";
        $headers["Return-Path"] = $from;
        $headers["Content-Type"] = "text/plain; charset=us-ascii";
        $headers["X-Application"] = "Gestao Financeira";
        return mail($to, $subject, str_replace("\n.\n", "\n..\n", $body), $headers, "-f {$from}");
    }
}
