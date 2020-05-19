<?php

namespace Weble\LaravelEcommerce\Customer;

use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\Enum\Exceptions\InvalidValueException;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\AddressType;
use Weble\LaravelEcommerce\Address\StoreAddress;

class Customer extends DataTransferObject implements Jsonable
{
    public string $id;
    public ?Authenticatable $user = null;
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

        $parameters['id'] ??= (string)Str::orderedUuid();

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
        } catch (InvalidValueException $e) {
            $taxAddressType = AddressType::shipping();
        }

        if ($taxAddressType->isEqual(AddressType::shipping())) {
            return $this->shippingAddress;
        }

        if ($taxAddressType->isEqual(AddressType::billing())) {
            return $this->billingAddress;
        }

        return new StoreAddress();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
