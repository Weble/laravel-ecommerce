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

trait InteractsWithStorage
{
    protected StorageInterface $storage;

    public function storage(): StorageInterface
    {
        return $this->storage;
    }

    protected function persist(string $key, $value): self
    {
        $this->storage()->set("{$this->instanceName()}.{$key}", $value);

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
            $this->storage()->get("{$this->instanceName()}.items", [])
        )->keyBy(fn (CartItem $item) => $item->getId());
    }

    protected function loadDiscountsFromStorage(): void
    {
        $this->discounts = $this->storage()->get(
            "{$this->instanceName()}.discounts",
            DiscountCollection::make(),
        );
    }

    protected function loadCustomerFromStorage(): void
    {
        $this->customer = $this->storage()->get(
            "{$this->instanceName()}.customer",
            new Customer([
                'id'              => (string) Str::uuid(),
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
