<?php

namespace Weble\LaravelEcommerce\Cart;

abstract class CartDriver implements CartDriverInterface
{
    protected string $instanceName;

    public function __construct(string $instanceName, array $config = [])
    {
        $this->instanceName = $instanceName;
    }

    public function instanceName(): string
    {
        return $this->instanceName;
    }
}
