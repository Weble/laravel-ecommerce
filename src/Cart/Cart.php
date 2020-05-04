<?php


namespace Weble\LaravelEcommerce\Cart;


class Cart
{
    protected CartDriverInterface $driver;

    public function __construct(CartDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function __call($name, $arguments)
    {
        return $this->driver->$name(...$arguments);
    }

    public function driver(): CartDriverInterface
    {
        return $this->driver;
    }
}
