<?php

namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Price\HasTotals;
use Weble\LaravelEcommerce\Purchasable;

interface CartInterface extends HasTotals, Arrayable
{
    public function instanceName(): string;

    public function get(string $cartItemId): CartItem;

    public function has(string $cartItemId): bool;

    public function clear(): CartInterface;

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartItem;

    public function remove(CartItem $cartItem): CartInterface;

    public function update(CartItem $cartItem): CartInterface;
}
