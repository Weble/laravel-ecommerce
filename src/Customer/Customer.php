<?php

namespace Weble\LaravelEcommerce\Customer;

use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;

class Customer extends Data
{
    public function __construct(
        public ?string $id = null,
        public $user = null,
        public ?string $email = null,
        public ?Address $billingAddress = null,
        public ?Address $shippingAddress = null,
    )
    {

        $this->billingAddress ??= new Address(
            type: AddressType::Billing,
        );

        $this->shippingAddress ??= new Address(
            type: AddressType::Shipping,
        );

        $this->id ??= sha1((string)Str::orderedUuid());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function taxAddress(): AddressInterface
    {
        $taxAddressType = config('ecommerce.tax.address_type', AddressType::Shipping);

        if ($taxAddressType === AddressType::Shipping) {
            return $this->shippingAddress;
        }

        if ($taxAddressType === AddressType::Billing) {
            return $this->billingAddress;
        }

        return new StoreAddress();
    }
}
