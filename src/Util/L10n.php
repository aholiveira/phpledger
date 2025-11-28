<?php
namespace PHPLedger\Util;
class L10n
{
    public static ?string $forcedLang = null;
    public static string $lang;
    private static array $l10n;

    public static function init(): void
    {
        self::$lang = self::$forcedLang ?? self::detectUserLang();
        self::$l10n = self::loadLang(self::$forcedLang);
    }
    public static function l(string $translationId, mixed ...$replacements): string
    {
        if (empty(self::$l10n[$translationId])) {
            return "";
        }
        $text = !empty($replacements)
            ? \sprintf(self::$l10n[$translationId], ...$replacements)
            : self::$l10n[$translationId];
        return !empty($text) ? htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', false) : "";
    }
    public static function html(): string
    {
        return match (true) {
            self::$lang === 'en-us' => 'en-US',
            self::$lang === 'pt-pt' => 'pt-PT',
            default => 'pt-PT'
        };
    }
    private static function detectUserLang(): string
    {
        $requested = strtolower($_REQUEST['lang'] ?? '');
        $browser = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        return strtolower(match (true) {
            $requested === 'en-us' => 'en-US',
            $requested === 'pt-pt' => 'pt-PT',
            str_starts_with($browser, 'en') => 'en-US',
            default => 'pt-PT'
        });
    }

    private static function loadLang(?string $forcedLang = null): array
    {
        $lang = $forcedLang ?? self::detectUserLang();
        $path = strtolower(ROOT_DIR . "/lang/$lang.php");
        if (!file_exists($path)) {
            $lang = 'pt-pt';
            $path = strtolower(ROOT_DIR . "/lang/$lang.php");
        }
        if (!file_exists($path)) {
            return [];
        }
        $langData = include $path;
        return \is_array($langData) ? $langData : [];
    }
}
