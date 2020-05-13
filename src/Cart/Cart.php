<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountInterface;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;
use Weble\LaravelEcommerce\Storage\StorageInterface;

class Cart implements CartInterface
{
    protected StorageInterface $storage;
    protected Customer $customer;
    protected DiscountCollection $discounts;
    protected string $instanceName;

    public function __construct(StorageInterface $storage, string $instanceName = 'cart')
    {
        $this->storage = $storage;
        $this->instanceName = $instanceName;
        $this->discounts = DiscountCollection::make([]);
        $this->customer = new Customer();
    }

    public function withDiscount(DiscountInterface $discount): self
    {
        if ($discount->target()->isEqual(DiscountTarget::item())) {
            throw new InvalidDiscountException();
        }

        $this->discounts->add($discount);

        return $this->persist("discounts", $this->discounts());
    }

    public function instanceName(): string
    {
        return $this->instanceName;
    }

    public function storage(): StorageInterface
    {
        return $this->storage;
    }

    public function discounts(): DiscountCollection
    {
        return $this->discounts->merge($this->items()->map(function (CartItem $cartItem) {
            return $cartItem->discounts;
        })->flatten());
    }

    public function get(string $id): CartItem
    {
        return new CartItem($this->storage()->get($id));
    }

    public function has(string $id): bool
    {
        return $this->storage()->has($id);
    }

    public function clear(): self
    {
        $this->storage()->remove($this->instanceName());

        return $this;
    }

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartItem
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $cartItem = CartItem::fromPurchasable($purchasable, $quantity, $attributes);

        $items = $this->items();
        if ($items->has($cartItem->getId())) {
            $cartItem->quantity += $items->get($cartItem->getId())->quantity;
        }

        $items->put($cartItem->getId(), $cartItem);
        $this->persist("items", $items);

        return $cartItem;
    }

    public function update(CartItem $cartItem): self
    {
        $items = $this->items();
        if (! $items->has($cartItem->getId())) {
            return $this;
        }

        $items->put($cartItem->getId(), $cartItem);
        $this->persist("items", $items);

        return $this;
    }

    public function remove(CartItem $cartItem): self
    {
        $items = $this->items();
        if (! $items->has($cartItem->getId())) {
            return $this;
        }

        $items = $items->except($cartItem->getId());
        $this->persist("items", $items);

        return $this;
    }

    public function items(): CartItemCollection
    {
        return CartItemCollection::make($this->storage()->get("{$this->instanceName()}.items", []));
    }

    public function discount(): Money
    {
        return Money::sum(
            $this->discounts->withTarget(DiscountTarget::items())->total($this->itemsSubtotal()),
            $this->discounts->withTarget(DiscountTarget::subtotal())->total($this->subTotalWithoutDiscounts())
        );
    }

    public function subTotalWithoutDiscounts(): Money
    {
        return $this->itemsSubtotal();
    }

    public function itemsSubtotal(): Money
    {
        return $this->items()->reduce(function (?Money $sum = null, ?CartItem $cartItem = null) {
            if ($sum === null) {
                return $cartItem->subTotal();
            }

            return $sum->add($cartItem->subTotal());
        });
    }

    public function subTotal(): Money
    {
        return $this->subTotalWithoutDiscounts()->subtract($this->discount());
    }

    public function tax(): Money
    {
        return $this->items()->reduce(function (?Money $sum = null, ?CartItem $cartItem = null) {
            if ($sum === null) {
                return $cartItem->tax($this->customer->taxAddress());
            }

            return $sum->add($cartItem->tax($this->customer->taxAddress()));
        });
    }

    public function total(): Money
    {
        return $this->subTotal()->add($this->tax());
    }

    protected function persist(string $key, $value): self
    {
        $this->storage()->set("{$this->instanceName()}.{$key}", $value);

        return $this;
    }
}
