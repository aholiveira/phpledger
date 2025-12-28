<?php

namespace PHPLedger\Services;

use PHPLedger\Exceptions\ConfigException;

final class ConfigValidator
{
    protected string $validationMessage = "";
    protected array $cfg;

    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
    }

    public function getValidationMessage(): string
    {
        return $this->validationMessage;
    }

    public function validate(bool $test = false): bool
    {
        try {
            $this->validateField('version', 'numeric', $test);
            $this->validateField('title', 'string', $test, true);
            $this->validateStorage($this->cfg['storage'] ?? [], $test);
            $this->validateSmtpSettings($this->cfg['smtp'] ?? [], $test);
            $this->validateAdminSettings($this->cfg['admin'] ?? [], $test);
            return true;
        } catch (ConfigException $e) {
            $this->handleValidationException($e);
            return false;
        }
    }

    private function validateField(string $key, string $type, bool $test, bool $nonEmpty = false)
    {
        if ($test) {
            return;
        }
        $value = $this->cfg[$key] ?? null;
        if ($value === null) {
            throw new ConfigException("'$key'", ConfigException::MISSING);
        }
        if ($type === 'numeric' && !is_numeric($value)) {
            throw new ConfigException("'$key'", ConfigException::INVALID);
        }
        if ($type === 'string'  && !is_string($value) || ($nonEmpty && trim($value) === '')) {
            throw new ConfigException("'$key'", ConfigException::INVALID);
        }
    }

    private function handleValidationException(ConfigException $e)
    {
        $code = $e->getCode();
        $msg = $e->getMessage();
        $this->validationMessage = match (true) {
            ($code & ConfigException::INVALID) && ($code & ConfigException::MISSING) => "Invalid or missing $msg",
            $code & ConfigException::INVALID => "Invalid $msg",
            $code & ConfigException::MISSING => "Missing $msg",
            $code & ConfigException::UNSUPPORTED => "Unsupported $msg",
            default => $msg,
        };
    }

    private function validateStorage(array $storage, bool $test = false): bool
    {
        if (!isset($storage['type'])) {
            throw new ConfigException("'storage.type'", ConfigException::INVALID + ConfigException::MISSING);
        }
        if (!$test && $storage['type'] !== 'mysql') {
            throw new ConfigException("'storage.type'", ConfigException::UNSUPPORTED);
        }
        if (!isset($storage['settings']) || !is_array($storage['settings'])) {
            throw new ConfigException("'storage.settings'", ConfigException::INVALID + ConfigException::MISSING);
        }
        return $storage['type'] === 'mysql' ? $this->validateMySqlStorage($storage['settings'], $test) : false;
    }

    private function validateMySqlStorage(array $settings, bool $test = false): bool
    {
        if ($test) {
            return true;
        }
        foreach (['host', 'database', 'user'] as $k) {
            if (empty($settings[$k]) || !is_string($settings[$k])) {
                throw new ConfigException("'storage.settings.$k'", ConfigException::INVALID + ConfigException::MISSING);
            }
        }
        if (isset($settings['port']) && (!is_numeric($settings['port']) || (int)$settings['port'] === 0)) {
            throw new ConfigException("'storage.settings.port'", ConfigException::INVALID);
        }
        return true;
    }

    private function validateSmtpSettings(array $settings, bool $test = false): bool
    {
        if ($test) {
            return true;
        }
        $this->validateFieldInArray($settings, 'host', 'string', true);
        $this->validateFieldInArray($settings, 'from', 'email', true);
        if (isset($settings['port']) && (!is_numeric($settings['port']) || (int)$settings['port'] === 0)) {
            throw new ConfigException("'smtp.port'", ConfigException::INVALID);
        }
        return true;
    }

    private function validateAdminSettings(array $settings, bool $test = false): bool
    {
        if ($test) {
            return true;
        }
        foreach (['username', 'password'] as $k) {
            if (empty($settings[$k])) {
                throw new ConfigException("'admin.$k'", ConfigException::INVALID + ConfigException::MISSING);
            }
        }
        return true;
    }

    private function validateFieldInArray(array $arr, string $key, string $type, bool $nonEmpty = false)
    {
        $value = $arr[$key] ?? null;
        if ($value === null) {
            throw new ConfigException("'$key'", ConfigException::MISSING);
        }
        if ($type === 'string' && (!is_string($value) || ($nonEmpty && trim($value) === ''))) {
            throw new ConfigException("'$key'", ConfigException::INVALID);
        }
        if ($type === 'email' && (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL))) {
            throw new ConfigException("'$key'", ConfigException::INVALID);
        }
    }
}
