<?php

namespace Weble\LaravelEcommerce\Customer;

use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;

class Customer extends DataTransferObject implements Jsonable
{
    public string $id;
    public $user = null;
    public ?string $email;
    public ?Address $billingAddress;
    public ?Address $shippingAddress;

    public function __construct(array $parameters = [])
    {
        if (isset($parameters['billingAddress']) && is_array($parameters['billingAddress'])) {
            $parameters['billingAddress'] = new Address($parameters['billingAddress']);
        }

        if (isset($parameters['shippingAddress']) && is_array($parameters['shippingAddress'])) {
            $parameters['shippingAddress'] = new Address($parameters['shippingAddress']);
        }

        $parameters['billingAddress'] ??= new Address([
            'type' => AddressType::Billing,
        ]);
        $parameters['shippingAddress'] ??= new Address([
            'type' => AddressType::Shipping,
        ]);

        $parameters['id'] ??= sha1((string)Str::orderedUuid());

        parent::__construct($parameters);
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

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
