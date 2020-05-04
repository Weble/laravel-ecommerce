<?php


namespace Weble\LaravelEcommerce\Cart;

use Closure;
use Illuminate\Foundation\Application;
use InvalidArgumentException;

class CartManager
{
    protected Application $app;
    protected array $instances = [];
    protected array $customCreators = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function instance(?string $name = null): Cart
    {
        $name = $name ?: $this->getDefaultInstance();
        return $this->instances[$name] = $this->get($name);
    }

    public function set(string $name, $instance): self
    {
        $this->instances[$name] = $instance;

        return $this;
    }

    public function getDefaultInstance(): string
    {
        return $this->app['config']['ecommerce.default_cart_instance'] ?? 'cart';
    }

    public function extend($driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    public function __call($method, $parameters)
    {
        return $this->instance()->$method(...$parameters);
    }

    protected function get(string $name)
    {
        return $this->instances[$name] ?? new Cart($this->resolve($name));
    }

    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Instance [{$name}] does not have a configured driver.");
        }

        $driver = $config['driver'];

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($name, $config);
        }

        $driverMethod = 'create' . ucfirst($driver) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        } else {
            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }
    }

    public function createSessionDriver(string $instanceName, array $config): CartSessionDriver
    {
        return new CartSessionDriver($instanceName, $config);
    }

    protected function callCustomCreator(string $instanceName, array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $instanceName, $config);
    }

    protected function getConfig(string $name): array
    {
        return $this->app['config']["ecommerce.cart_instances.{$name}"] ?: [];
    }
}
