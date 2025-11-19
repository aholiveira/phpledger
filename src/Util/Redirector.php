<?php
namespace PHPLedger\Util;
class Redirector
{
    private const array ALLOWED_REDIRECTS = [
        'ledger_entries.php',
        'account_types_list.php',
        'account_types.php',
        'balances.php',
        'entry_type.php',
        'report_month.php',
        'report_year.php',
        'update.php'
    ];
    public static function to($url, $delay = 0): never
    {
        $url_path = strtok($url, '?'); // strip query params
        if (!\in_array($url_path, self::ALLOWED_REDIRECTS, true)) {
            $url = "ledger_entries.php";
        }
        if (!headers_sent()) {
            header("Location: $url", true, 303);
        } else {
            echo "<meta http-equiv='REFRESH' content='{$delay}; URL=\"{$url}\"'>";
            echo "<noscript><a href=\"{$url}\">Clique aqui para continuar</a></noscript>";
        }
        exit;
    }
}
