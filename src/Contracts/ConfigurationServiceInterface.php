<?php

namespace PHPLedger\Contracts;

interface ConfigurationServiceInterface
{
    public static function init(string $configfile, bool $test = false): bool;
    public static function load(string $configfile, bool $test = false): array;
    public static function loaded(): bool;
    public function set(string $setting, mixed $value): void;
    public function get(string $setting, mixed $default = null): mixed;
    public function validate(array $data, bool $test = false): bool;
    public function save(): void;
    public function getCurrent(): array;
    public function getValidationMessage(): string;
    public function getConfigFilePath(): string;
}
