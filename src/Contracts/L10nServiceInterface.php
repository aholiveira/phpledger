<?php

namespace PHPLedger\Contracts;

interface L10nServiceInterface
{
    public function html(): string;
    public function l(string $translationId, mixed ...$replacements): string;
    public function lang(): string;
    public function pl(string $translationId, mixed ...$replacements): void;
    public function sanitizeLang(?string $lang): string;
    public function setLang(string $lang): void;
}
