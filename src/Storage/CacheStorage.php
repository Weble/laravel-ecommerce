<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CacheStorage implements StorageInterface
{
    protected Repository $cache;
    protected string $prefix;
    protected string $instanceName;

    public function __construct(array $config = [])
    {
        $driver = $config['driver'] ?? 'default';

        if ($driver === 'default') {
            $driver = null;
        }

        $sessionKey = $config['session_key'] ?? 'ecommerce.store.cache.';

        $sessionPrefix = session()->get($sessionKey, Str::uuid());

        $this->cache  = Cache::store($driver);
        $this->prefix = $sessionPrefix . "." . ($config['prefix'] ?? 'ecommerce.');
    }

    public function setInstanceName(string $name): StorageInterface
    {
        $this->instanceName = $name;
        $this->prefix .= '.' . $this->instanceName;

        return $this;
    }

    public function set(string $key, $value): StorageInterface
    {
        $this->cache->set($this->prefix . $key, $value);

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get($this->prefix . $key, $default);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($this->prefix . $key);
    }

    public function remove(string $key): StorageInterface
    {
        $this->cache->forget($this->prefix . $key);

        return $this;
    }
}
