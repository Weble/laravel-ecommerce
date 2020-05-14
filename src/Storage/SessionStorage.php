<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;

class SessionStorage implements StorageInterface
{
    protected Store $session;
    protected string $prefix;
    protected string $instanceName;

    public function __construct(array $config = [])
    {
        $driver = $config['driver'] ?? 'default';

        if ($driver === 'default') {
            $driver = null;
        }

        $this->session = app()->make(SessionManager::class)->driver($driver);
        $this->prefix = $config['prefix'] ?? 'ecommerce.';
    }

    public function setInstanceName(string $name): StorageInterface
    {
        $this->instanceName = $name;
        $this->prefix .= '.' . $this->instanceName;

        return $this;
    }

    public function set(string $key, $value): self
    {
        $this->session->put($this->prefix . $key, $value);

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $this->session->get($this->prefix . $key, $default);
    }

    public function has(string $key): bool
    {
        return $this->session->has($this->prefix . $key);
    }

    public function remove(string $key): self
    {
        $this->session->put($this->prefix . $key, null);

        return $this;
    }
}
