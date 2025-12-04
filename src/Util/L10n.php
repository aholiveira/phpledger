<?php

namespace PHPLedger\Util;

class L10n
{
    public static ?string $forcedLang = null;
    // sensible defaults so methods can be used before init() in tests or scripts
    public static string $lang = 'pt-pt';
    private static array $l10n = [];
    // simple in-memory cache for loaded language files
    private static array $cache = [];

    public static function init(): void
    {
        // If a forced language is set, prefer it; otherwise detect from request/browser
        self::$lang = self::$forcedLang ?? self::detectUserLang();
        self::$lang = self::normalizeLang(self::$lang);
        self::$l10n = self::loadLang(self::$forcedLang);
    }

    /**
     * Force the active language (useful in tests).
     */
    public static function setLang(string $lang): void
    {
        $norm = self::normalizeLang($lang);
        self::$forcedLang = $norm;
        self::$lang = $norm;
        self::$l10n = self::loadLang(self::$forcedLang);
    }

    public static function l(string $translationId, mixed ...$replacements): string
    {
        // ensure translations are loaded
        if (empty(self::$l10n)) {
            self::init();
        }

        if (empty(self::$l10n[$translationId])) {
            return "";
        }

        $text = !empty($replacements)
            ? self::safeSprintf(self::$l10n[$translationId], $replacements)
            : self::$l10n[$translationId];

        return $text !== null && $text !== ''
            ? htmlspecialchars((string) $text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', false)
            : "";
    }

    public static function pl(string $translationId, mixed ...$replacements): void
    {
        print self::l($translationId, ...$replacements);
    }

    public static function html(): string
    {
        // ensure $lang has a value
        if (empty(self::$lang)) {
            self::init();
        }

        $lang = self::normalizeLang(self::$lang);
        return match ($lang) {
            'en-us' => 'en-US',
            'pt-pt' => 'pt-PT',
            default => 'pt-PT'
        };
    }
    private static function detectUserLang(): string
    {
        $requested = strtolower($_REQUEST['lang'] ?? '');
        $browser = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        $match = match (true) {
            $requested === 'en-us' => 'en-us',
            $requested === 'pt-pt' => 'pt-pt',
            str_starts_with($browser, 'en') => 'en-us',
            default => 'pt-pt'
        };
        return self::normalizeLang($match);
    }

    private static function loadLang(?string $forcedLang = null): array
    {
        $lang = self::normalizeLang($forcedLang ?? self::detectUserLang());

        if (isset(self::$cache[$lang])) {
            return self::$cache[$lang];
        }

        $file = Path::combine(ROOT_DIR, 'lang', $lang . '.json');
        if (!file_exists($file)) {
            $lang = 'pt-pt';
            $file = Path::combine(ROOT_DIR, 'lang', $lang . '.json');
        }

        if (!file_exists($file)) {
            self::$cache[$lang] = [];
            return [];
        }
        Logger::instance()->debug("Loading lang from $file", __CLASS__);
        $json = file_get_contents($file);
        $langData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $arr = \is_array($langData) ? $langData : [];
        self::$cache[$lang] = $arr;
        return $arr;
    }
    public static function sanitizeLang(?string $lang): string
    {
        $allowed = ['en', 'pt', 'pt-pt', 'en-us'];
        return in_array(strtolower($lang ?? ''), $allowed, true) ? $lang : L10n::$lang;
    }
    private static function normalizeLang(string $lang): string
    {
        return strtolower(str_replace('_', '-', trim($lang)));
    }

    /**
     * Safely format a translation with replacements. If placeholder count and
     * replacements mismatch, fall back to a safe concatenation to avoid warnings.
     */
    private static function safeSprintf(string $format, array $replacements): string
    {
        // count non-escaped placeholders like %s, %d, %f, etc.
        preg_match_all('/(?<!%)%[bcdeEfFgGosuxX]/', $format, $m);
        $expected = count($m[0]);
        if ($expected !== count($replacements)) {
            // avoid sprintf warnings: return format plus joined replacements
            return $format . (count($replacements) ? ' ' . implode(' ', array_map('strval', $replacements)) : '');
        }
        // safe to apply
        return vsprintf($format, $replacements);
    }
}
