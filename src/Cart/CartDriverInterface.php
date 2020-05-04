<?php


namespace Weble\LaravelEcommerce\Cart;


use Weble\LaravelEcommerce\Purchasable;

interface CartDriverInterface
{
    public function instanceName(): string;

    public function add(Purchasable $product, float $quantity = 1): self;

    public function get(Purchasable $product): Purchasable;

    public function has(Purchasable $product): bool;

    public function remove(Purchasable $product): self;

    public function clear(): self;
}
