<?php

namespace PHPLedger\Util;

class Redirector
{
    private const array ALLOWED_REDIRECTS = ['index.php'];
    public static function to($url, $delay = 0): void
    {
        $url_path = strtok($url, '?'); // strip query params
        if (!\in_array(basename($url_path), self::ALLOWED_REDIRECTS, true)) {
            $url = "index.php";
        }
        if (!headers_sent()) {
            header($delay > 0 ? "Refresh: $delay; URL=$url" : "Location: $url", true, 303);
        } else {
            echo "<meta http-equiv='REFRESH' content='{$delay}; URL=\"{$url}\"'>";
            echo "<noscript><a href=\"{$url}\">Clique aqui para continuar</a></noscript>";
        }
    }
}
