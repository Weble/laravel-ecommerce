<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Cart\Concern\InteractsWithStorage;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;
use Weble\LaravelEcommerce\Storage\StorageInterface;

class Cart implements CartInterface, Arrayable, Jsonable
{
    use InteractsWithStorage;

    protected CartItemCollection $items;
    protected Customer $customer;
    protected DiscountCollection $discounts;
    protected string $instanceName;

    public function __construct(StorageInterface $storage, string $instanceName = 'cart')
    {
        $this->instanceName = $instanceName;

        $this->storage = $storage;
        $this->storage()->setInstanceName($instanceName);
        $this->loadFromStorage();
    }

    public function instanceName(): string
    {
        return $this->instanceName;
    }

    public function items(): CartItemCollection
    {
        return $this->items;
    }

    public function discounts(): DiscountCollection
    {
        return $this->discounts->merge($this->items()->map(function (CartItem $cartItem) {
            return $cartItem->discounts;
        })->flatten());
    }

    public function customer(): Customer
    {
        return $this->customer;
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
        if (! $this->items()->has($cartItem->getId())) {
            return $this;
        }

        $this->items = $this->items()->except($cartItem->getId());
        $this->persist("items", $this->items());

        return $this;
    }

    public function forCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this->persist("customer", $this->customer());
    }

    public function withDiscount(Discount $discount): self
    {
        if ($discount->target()->isEqual(DiscountTarget::item())) {
            throw new InvalidDiscountException();
        }

        $this->discounts->add($discount);

        return $this->persist("discounts", $this->discounts());
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
        if ($this->items()->count() <= 0) {
            return money(0);
        }

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
        if ($this->items()->count() <= 0) {
            return money(0);
        }

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

    public function toArray()
    {
        return [
            'items' => $this->items()->toArray(),
            'discounts' => $this->discounts()->toArray(),
            'subtotal' => $this->subTotal()->toArray(),
            'tax' => $this->tax()->toArray(),
            'total' => $this->tax()->toArray(),
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
