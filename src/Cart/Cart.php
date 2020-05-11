<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Support\Collection;
use Spatie\Enum\Exceptions\InvalidValueException;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountInterface;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;

class Cart implements CartInterface
{
    protected CartDriverInterface $driver;
    protected ?AddressInterface $billingAddress = null;
    protected ?AddressInterface $shippingAddress = null;
    protected DiscountCollection $discounts;

    public function __construct(CartDriverInterface $driver)
    {
        $this->driver = $driver;
        $this->discounts = DiscountCollection::make([]);
    }

    public function withDiscount(DiscountInterface $discount): self
    {
        if ($discount->target()->isEqual(DiscountTarget::item())) {
            throw new InvalidDiscountException();
        }

        $this->discounts->add($discount);

        return $this;
    }

    public function setBillingAddress(AddressInterface $address): self
    {
        $this->billingAddress = $address;

        return $this;
    }

    public function setShippingAddress(AddressInterface $address): self
    {
        $this->shippingAddress = $address;

        return $this;
    }

    public function billingAddress(): ?AddressInterface
    {
        return $this->billingAddress;
    }

    public function shippingAddress(): ?AddressInterface
    {
        return $this->shippingAddress;
    }

    public function hasBillingAddress(): bool
    {
        return $this->billingAddress() !== null;
    }

    public function hasShippingAddress(): bool
    {
        return $this->shippingAddress() !== null;
    }

    public function taxAddress(): AddressInterface
    {
        try {
            $taxAddressType = AddressType::make(config('ecommerce.tax.address_type', 'shipping'));
        } catch (InvalidValueException $e) {
            $taxAddressType = AddressType::shipping();
        }

        if ($this->hasShippingAddress() && $taxAddressType->isEqual(AddressType::shipping())) {
            return $this->shippingAddress();
        }

        if ($this->hasBillingAddress() && $taxAddressType->isEqual(AddressType::billing())) {
            return $this->billingAddress();
        }

        return new StoreAddress();
    }

    public function driver(): CartDriverInterface
    {
        return $this->driver;
    }

    public function discounts(): DiscountCollection
    {
        return $this->discounts->merge($this->items()->map(function (CartItem $cartItem) {
            return $cartItem->discounts;
        })->flatten());
    }

    public function instanceName(): string
    {
        return $this->driver()->instanceName();
    }

    public function get(string $id): CartItem
    {
        return $this->driver()->get($id);
    }

    public function has(string $id): bool
    {
        return $this->driver()->has($id);
    }

    public function clear(): self
    {
        $this->driver()->clear();

        return $this;
    }

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartItem
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $cartItem = CartItem::fromPurchasable($purchasable, $quantity, $attributes);

        if ($this->driver->has($cartItem->getId())) {
            $cartItem->quantity += $this->driver()->get($cartItem->getId())->quantity;
        }

        $this->driver()->set($cartItem);

        return $cartItem;
    }

    public function update(CartItem $cartItem): self
    {
        if (! $this->driver()->has($cartItem->getId())) {
            return $this;
        }

        $this->driver()->set($cartItem);

        return $this;
    }

    public function remove(CartItem $cartItem): self
    {
        if (! $this->driver()->has($cartItem->getId())) {
            return $this;
        }

        $this->driver()->remove($cartItem);

        return $this;
    }

    public function items(): CartItemCollection
    {
        return $this->driver->items();
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
                return $cartItem->tax($this->taxAddress());
            }

            return $sum->add($cartItem->tax($this->taxAddress()));
        });
    }

    public function total(): Money
    {
        return $this->subTotal()->add($this->tax());
    }
}
