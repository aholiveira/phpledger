<?php

namespace PHPLedger\Contracts;

interface ConfigurationServiceInterface
{
    public static function init(string $configfile, bool $test = false): bool;
    public function load(string $configfile, bool $test = false): void;
    public function set(string|array $setting, mixed $value): void;
    public function get(string $setting, mixed $default = null): mixed;
    public function validate(array $data): bool;
    public function getList(array $list): array;
    public function save(): void;
}
