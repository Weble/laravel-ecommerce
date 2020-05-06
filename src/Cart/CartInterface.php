<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Purchasable;

interface CartInterface
{
    public function __construct(CartDriverInterface $driver);

    public function driver(): CartDriverInterface;

    public function instanceName(): string;

    public function get(CartItem $cartItem): CartItem;

    public function has(CartItem $cartItem): bool;

    public function clear(): CartInterface;

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartInterface;

    public function remove(CartItem $cartItem): CartInterface;

    public function subTotal(): Money;
}
