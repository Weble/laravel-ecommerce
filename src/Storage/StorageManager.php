<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Support\Manager;

class StorageManager extends Manager
{
    public function store(?string $name = null): StorageInterface
    {
        return $this->driver($name);
    }

    public function getDefaultDriver(): string
    {
        return $this->config['ecommerce.storage.default'] ?? 'session';
    }

    public function createSessionDriver(): SessionStorage
    {
        $config = $this->getConfig('session');

        return new SessionStorage($config);
    }

    public function createCacheDriver(): CacheStorage
    {
        $config = $this->getConfig('cache');

        return new CacheStorage($config);
    }

    public function createEloquentDriver(): EloquentStorage
    {
        $config = $this->getConfig('eloquent');

        return new EloquentStorage($config, $this->config['ecommerce.classes'] ?? []);
    }

    protected function getConfig(string $storeName): array
    {
        return $this->config['ecommerce.storage.stores.' . $storeName] ?? [];
    }
}
