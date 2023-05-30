<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class StorageManager extends Manager
{
    public function store(?string $driver = null, ?string $name = null): StorageInterface
    {
        return $this->driver($driver, $name);
    }

    public function driver($driver = null, $name = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();
        $name = $name ?: $driver;

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver][$name])) {
            $this->drivers[$driver][$name] = $this->createDriver($driver)->setInstanceName($name);
        }

        return $this->drivers[$driver][$name];
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
