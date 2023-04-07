<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Cart\Concern\InteractsWithStorage;
use Weble\LaravelEcommerce\Cart\Event\CartItemAdded;
use Weble\LaravelEcommerce\Cart\Event\CartItemRemoved;
use Weble\LaravelEcommerce\Cart\Event\CartItemUpdated;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;
use Weble\LaravelEcommerce\Storage\StorageInterface;
use Weble\LaravelEcommerce\Storage\StorageType;

class Cart implements CartInterface, Jsonable
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
        return $this->items()->get($id);
    }

    public function has(string $id): bool
    {
        return $this->storage()->get(StorageType::Items->value, collect([]))->has($id);
    }

    public function clear(): self
    {
        $this->storage()->remove(StorageType::Items->value);
        $this->storage()->remove(StorageType::Discounts->value);

        $this->items     = CartItemCollection::make([]);
        $this->discounts = DiscountCollection::make([]);

        return $this;
    }

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null, ?Money $price = null): CartItem
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $cartItem = CartItem::fromPurchasable($purchasable, $quantity, $attributes);
        if ($price !== null) {
            $cartItem->price = $price;
        }

        if ($this->items()->has($cartItem->getId())) {
            $cartItem->quantity += $this->items()->get($cartItem->getId())->quantity;
        }

        $this->items()->put($cartItem->getId(), $cartItem);
        $this->persist(StorageType::Items->value, $this->items());

        event(new CartItemAdded($cartItem, $this->instanceName()));

        return $cartItem;
    }

    public function update(CartItem $cartItem): self
    {
        if (! $this->items()->has($cartItem->getId())) {
            return $this;
        }

        $this->items()->put($cartItem->getId(), $cartItem);
        $this->persist(StorageType::Items->value, $this->items());

        event(new CartItemUpdated($cartItem, $this->instanceName()));

        return $this;
    }

    public function remove(CartItem $cartItem): self
    {
        if (! $this->items()->has($cartItem->getId())) {
            return $this;
        }

        $this->items = $this->items()->except($cartItem->getId());
        $this->persist(StorageType::Items->value, $this->items());

        event(new CartItemRemoved($cartItem, $this->instanceName()));

        return $this;
    }

    public function forCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this->persist(StorageType::Customer->value, $this->customer());
    }

    public function withDiscount(Discount $discount): self
    {
        if ($discount->target === DiscountTarget::Item) {
            throw new InvalidDiscountException();
        }

        $this->discounts->add($discount);

        return $this->persist(StorageType::Discounts->value, $this->discounts());
    }

    public function removeDiscounts($keys): self
    {
        $this->discounts = $this->discounts->except($keys);
        $this->persist(StorageType::Discounts->value, $this->discounts());

        return $this;
    }

    public function clearDiscounts(): self
    {
        $this->discounts = new DiscountCollection([]);
        $this->items()->each(function(CartItem $item) {
            $item->clearDiscounts();
        });

        $this->persist(StorageType::Discounts->value, $this->discounts());
        $this->persist(StorageType::Items->value, $this->items());

        return $this;
    }

    public function discount(): Money
    {
        return Money::sum(
            $this->discounts->withTarget(DiscountTarget::Items)->total($this->itemsSubtotal()),
            $this->discounts->withTarget(DiscountTarget::Subtotal)->total($this->subTotalWithoutDiscounts())
        );
    }

    public function singleItemsDiscounts(): Money
    {
        return $this->items()->reduce(function (Money $carry, CartItem $item) {
            return $carry->add($item->discount());
        }, new Money(0, currencyManager()->defaultCurrency()));
    }

    public function subTotalWithoutDiscounts(): Money
    {
        return $this->itemsSubtotal();
    }

    public function itemsSubtotal(): Money
    {
        if ($this->items()->count() <= 0) {
            return new Money(0, currencyManager()->defaultCurrency());
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
            return new Money(0, currencyManager()->defaultCurrency());
        }

        $tax = $this->items()->reduce(function (?Money $sum = null, ?CartItem $cartItem = null) {
            if ($sum === null) {
                return $cartItem->tax($this->customer->taxAddress());
            }

            return $sum->add($cartItem->tax($this->customer->taxAddress()));
        });

        // discounts is just a collection of "subtotal" and "items" discount target types
        if ($this->discounts->count() > 0) {
            // proportionate discount also on tax
            return $tax
                ->multiply($this->subTotal()->getAmount())
                ->divide($this->subTotalWithoutDiscounts()->getAmount());
        }

        return $tax;
    }

    public function total(): Money
    {
        return $this->subTotal()->add($this->tax());
    }

    public function toArray()
    {
        return [
            'instance'  => $this->instanceName(),
            'items'     => $this->items()->toArray(),
            'discounts' => $this->discounts()->toArray(),
            'subtotal'  => $this->subTotal()->toArray(),
            'tax'       => $this->tax()->toArray(),
            'total'     => $this->total()->toArray(),
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
