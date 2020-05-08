<?php

namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Price\HasTotals;
use Weble\LaravelEcommerce\Purchasable;

interface CartInterface extends HasTotals
{
    public function __construct(CartDriverInterface $driver);

    public function driver(): CartDriverInterface;

    public function instanceName(): string;

    public function get(CartItem $cartItem): CartItem;

    public function has(CartItem $cartItem): bool;

    public function clear(): CartInterface;

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartItem;

    public function remove(CartItem $cartItem): CartInterface;
}
