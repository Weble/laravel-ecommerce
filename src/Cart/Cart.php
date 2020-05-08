<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;
use Weble\LaravelEcommerce\Purchasable;

class Cart implements CartInterface
{
    protected CartDriverInterface $driver;

    protected ?AddressInterface $billingAddress = null;
    protected ?AddressInterface $shippingAddress = null;

    public function __construct(CartDriverInterface $driver)
    {
        $this->driver = $driver;
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
        $taxAddressType = config('ecommerce.store.address_for_tax', 'shipping');

        $address = new StoreAddress();
        switch ($taxAddressType) {
            case AddressType::SHIPPING:
                if ($this->hasShippingAddress()) {
                    $address = $this->shippingAddress();
                }

                break;
            case AddressType::BILLING:
                if ($this->hasBillingAddress()) {
                    $address = $this->billingAddress();
                }

                break;
        }

        return $address;
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

    public function add(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): CartItem
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $cartItem = CartItem::fromPurchasable($purchasable, $quantity, $attributes);

        if ($this->driver->has($cartItem)) {
            $cartItem->quantity += $this->driver()->get($cartItem)->quantity;
        }

        $this->driver()->set($cartItem);

        return $cartItem;
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
