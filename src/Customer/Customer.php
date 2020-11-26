<?php

namespace Weble\LaravelEcommerce\Customer;

use BadMethodCallException;
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
        $parameters['billingAddress'] ??= new Address([
            'type' => AddressType::billing(),
        ]);
        $parameters['shippingAddress'] ??= new Address([
            'type' => AddressType::shipping(),
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
        try {
            $taxAddressType = AddressType::make(config('ecommerce.tax.address_type', 'shipping'));
        } catch (BadMethodCallException $e) {
            $taxAddressType = AddressType::shipping();
        }

        if ($taxAddressType->equals(AddressType::shipping())) {
            return $this->shippingAddress;
        }

        if ($taxAddressType->equals(AddressType::billing())) {
            return $this->billingAddress;
        }

        return new StoreAddress();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
