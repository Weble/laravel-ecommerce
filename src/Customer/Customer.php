<?php

namespace Weble\LaravelEcommerce\Customer;

use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Support\Jsonable;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\Enum\Exceptions\InvalidValueException;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;

class Customer extends DataTransferObject implements Jsonable
{
    public ?Authenticatable $user = null;
    public ?AddressInterface $billingAddress = null;
    public ?AddressInterface $shippingAddress = null;

    public function hasBillingAddress(): bool
    {
        return $this->billingAddress !== null;
    }

    public function hasShippingAddress(): bool
    {
        return $this->shippingAddress !== null;
    }

    public function taxAddress(): AddressInterface
    {
        try {
            $taxAddressType = AddressType::make(config('ecommerce.tax.address_type', 'shipping'));
        } catch (InvalidValueException $e) {
            $taxAddressType = AddressType::shipping();
        }

        if ($this->hasShippingAddress() && $taxAddressType->isEqual(AddressType::shipping())) {
            return $this->shippingAddress;
        }

        if ($this->hasBillingAddress() && $taxAddressType->isEqual(AddressType::billing())) {
            return $this->billingAddress;
        }

        return new StoreAddress();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
