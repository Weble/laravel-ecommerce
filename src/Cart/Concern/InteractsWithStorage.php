<?php

namespace Weble\LaravelEcommerce\Cart\Concern;

use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Cart\CartItem;
use Weble\LaravelEcommerce\Cart\CartItemCollection;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Storage\StorageInterface;
use Weble\LaravelEcommerce\Storage\StorageType;

trait InteractsWithStorage
{
    protected StorageInterface $storage;

    public function storage(): StorageInterface
    {
        return $this->storage;
    }

    protected function persist(string $key, $value): self
    {
        $this->storage()->set($key, $value);

        return $this;
    }

    public function loadFromStorage(): void
    {
        $this->loadItemsFromStorage();
        $this->loadCustomerFromStorage();
        $this->loadDiscountsFromStorage();
    }

    protected function loadItemsFromStorage(): void
    {
        $this->items = CartItemCollection::make(
            $this->storage()->get(StorageType::ITEMS, [])
        )->keyBy(fn (CartItem $item) => $item->getId());
    }

    protected function loadDiscountsFromStorage(): void
    {
        $this->discounts = $this->storage()->get(
            StorageType::DISCOUNTS,
            DiscountCollection::make(),
        );
    }

    protected function loadCustomerFromStorage(): void
    {
        $this->customer = $this->storage()->get(
            StorageType::CUSTOMER,
            new Customer([
                'id'              => (string) Str::orderedUuid(),
                'shippingAddress' => new Address([
                    'type' => AddressType::shipping(),
                ]),
                'billingAddress' => new Address([
                    'type' => AddressType::billing(),
                ]),
            ])
        );
    }
}
