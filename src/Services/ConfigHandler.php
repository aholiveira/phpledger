<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\ConfigurationServiceInterface;

final class ConfigHandler
{
    private ConfigurationServiceInterface $config;

    public function __construct(ConfigurationServiceInterface $config)
    {
        $this->config = $config;
    }

    public function getCurrent(): array
    {
        return $this->config->getCurrent();
    }

    public function validate(array $new): bool
    {
        return $this->config->validate($new);
    }

    public function save(array $new): void
    {
        $this->config->set('version', $new['version'], false);
        $this->config->set('title', $new['title'], false);
        $this->config->set('storage.type', $new['storage']['type'], false);
        $this->config->set('storage.settings', $new['storage']['settings'], false);
        $this->config->set('smtp', $new['smtp'], false);
        $this->config->set('admin', $new['admin'], false);
        $this->config->set('url', $new['url'], false);
        $this->config->save();
    }
}
