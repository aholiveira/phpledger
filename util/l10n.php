<?php
class l10n
{
    public static ?string $forced_lang = null;
    public static string $lang;
    private static array $l10n;

    public static function init(): void
    {
        self::$lang = self::$forced_lang ?? self::detect_user_lang();
        self::$l10n = self::load_lang(self::$forced_lang);
    }
    public static function l(string $translation_id): string
    {
        return htmlspecialchars(self::$l10n[$translation_id], ENT_QUOTES, 'UTF-8');
    }
    private static function detect_user_lang(): string
    {
        $requested = strtolower($_REQUEST['lang'] ?? '');
        $browser = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        return match (true) {
            $requested === 'en-us' => 'en-US',
            $requested === 'pt-pt' => 'pt-PT',
            str_starts_with($browser, 'en') => 'en-US',
            default => 'pt-PT'
        };
    }

    private static function load_lang(?string $forced_lang = null): array
    {
        $lang = $forced_lang ?? self::detect_user_lang();
        $path = strtolower(ROOT_DIR . "/lang/$lang.php");

        if (!file_exists($path)) {
            $lang = 'pt-PT';
            $path = strtolower(ROOT_DIR . "/lang/$lang.php");
        }

        return file_exists($path) ? include $path : [];
    }

}