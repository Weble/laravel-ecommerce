<?php


namespace Weble\LaravelEcommerce\Cart;

use Weble\LaravelEcommerce\Purchasable;

class CartSessionDriver extends CartDriver implements CartDriverInterface
{
    protected string $prefix;
    protected string $instanceName;

    public function __construct(string $instanceName, array $config = [])
    {
        parent::__construct($instanceName, $config);

        $this->prefix = $config['session_key_prefix'] ?? 'cart_';
    }

    public function add(Purchasable $product, float $quantity = 1): CartDriverInterface
    {
        // TODO: Implement add() method.
    }

    public function get(Purchasable $product): Purchasable
    {
        // TODO: Implement get() method.
    }

    public function has(Purchasable $product): bool
    {
        // TODO: Implement has() method.
    }

    public function remove(Purchasable $product): CartDriverInterface
    {
        // TODO: Implement remove() method.
    }

    public function clear(): CartDriverInterface
    {
        // TODO: Implement clear() method.
    }
}
