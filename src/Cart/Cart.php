<?php


namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Purchasable;

class Cart implements CartInterface
{
    protected CartDriverInterface $driver;

    public function __construct(CartDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function driver(): CartDriverInterface
    {
        return $this->driver;
    }

    public function instanceName(): string
    {
        return $this->driver()->instanceName();
    }

    public function get(CartItem $cartItem): CartItem
    {
        return $this->driver()->get($cartItem);
    }

    public function has(CartItem $cartItem): bool
    {
        return $this->driver()->has($cartItem);
    }

    public function clear(): self
    {
        $this->driver()->clear();

        return $this;
    }

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): self
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $cartItem = CartItem::fromPurchasable($purchasable, $quantity, $attributes);

        if ($this->driver->has($cartItem)) {
            $cartItem->quantity += $this->driver()->get($cartItem)->quantity;
        }

        $this->driver()->set($cartItem);

        return $this;
    }

    public function remove(CartItem $cartItem): self
    {
        if (! $this->driver()->has($cartItem)) {
            return $this;
        }

        $this->driver()->remove($cartItem);

        return $this;
    }


    public function items(): CartItemCollection
    {
        return $this->driver->items();
    }

    public function subTotal(): Money
    {
        return $this->items()->reduce(function (?Money $sum = null, ?CartItem $cartItem = null) {
            if ($sum === null) {
                return $cartItem->subTotal();
            }

            return $sum->add($cartItem->subTotal());
        });
    }
}
