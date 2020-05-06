<?php


namespace Weble\LaravelEcommerce\Cart;

interface CartDriverInterface
{
    public function instanceName(): string;

    public function set(CartItem $cartItem): self;

    public function get(CartItem $cartItem): CartItem;

    public function has(CartItem $cartItem): bool;

    public function remove(CartItem $cartItem): self;

    public function clear(): self;

    public function items(): CartItemCollection;
}
