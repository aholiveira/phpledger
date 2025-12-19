<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Services\Logger;
use PHPLedger\Util\Path;

class L10n implements L10nServiceInterface
{
    public ?string $forcedLang = null;
    // sensible defaults so methods can be used before init() in tests or scripts
    private string $lang = 'pt-pt';
    private array $l10n = [];
    // simple in-memory cache for loaded language files
    private array $cache = [];

    public function __construct()
    {
        // If a forced language is set, prefer it; otherwise detect from request/browser
        $this->lang = $this->forcedLang ?? $this->detectUserLang();
        $this->lang = $this->normalizeLang($this->lang);
        $this->l10n = $this->loadLang($this->forcedLang);
    }

    public function lang(): string
    {
        return $this->lang;
    }

    /**
     * Force the active language (useful in tests).
     */
    public function setLang(string $lang): void
    {
        $norm = $this->normalizeLang($lang);
        $this->forcedLang = $norm;
        $this->lang = $norm;
        $this->l10n = $this->loadLang($this->forcedLang);
    }

    public function l(string $translationId, mixed ...$replacements): string
    {
        if (empty($this->l10n[$translationId])) {
            return "";
        }

        $text = !empty($replacements)
            ? $this->safeSprintf($this->l10n[$translationId], $replacements)
            : $this->l10n[$translationId];

        return $text !== null && $text !== ''
            ? htmlspecialchars((string) $text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8', false)
            : "";
    }

    public function pl(string $translationId, mixed ...$replacements): void
    {
        print $this->l($translationId, ...$replacements);
    }

    public function html(): string
    {
        $lang = $this->normalizeLang($this->lang);
        return match ($lang) {
            'en-us' => 'en-US',
            'pt-pt' => 'pt-PT',
            default => 'pt-PT'
        };
    }
    private function detectUserLang(): string
    {
        $requested = strtolower($_REQUEST['lang'] ?? '');
        $browser = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        $match = match (true) {
            $requested === 'en-us' => 'en-us',
            $requested === 'pt-pt' => 'pt-pt',
            str_starts_with($browser, 'en') => 'en-us',
            default => 'pt-pt'
        };
        return $this->normalizeLang($match);
    }

    private function loadLang(?string $forcedLang = null): array
    {
        $lang = $this->normalizeLang($forcedLang ?? $this->detectUserLang());

        if (isset($this->cache[$lang])) {
            return $this->cache[$lang];
        }

        $file = Path::combine(ROOT_DIR, 'lang', $lang . '.json');
        if (!file_exists($file)) {
            $lang = 'pt-pt';
            $file = Path::combine(ROOT_DIR, 'lang', $lang . '.json');
        }

        if (!file_exists($file)) {
            $this->cache[$lang] = [];
            return [];
        }
        $json = file_get_contents($file);
        $langData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $arr = \is_array($langData) ? $langData : [];
        $this->cache[$lang] = $arr;
        return $arr;
    }
    public function sanitizeLang(?string $lang): string
    {
        $allowed = ['en', 'pt', 'pt-pt', 'en-us'];
        return in_array(strtolower($lang ?? ''), $allowed, true) ? $lang : $this->lang;
    }

    private function normalizeLang(string $lang): string
    {
        return strtolower(str_replace('_', '-', trim($lang)));
    }

    /**
     * Safely format a translation with replacements. If placeholder count and
     * replacements mismatch, fall back to a safe concatenation to avoid warnings.
     */
    private function safeSprintf(string $format, array $replacements): string
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
