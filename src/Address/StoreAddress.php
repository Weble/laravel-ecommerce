<?php

namespace Weble\LaravelEcommerce\Address;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressInterface;

class StoreAddress extends Address implements AddressInterface
{
    protected string $vatId = '';

    public function __construct()
    {
        parent::__construct(
            config('ecommerce.store.address.country', ''),
            config('ecommerce.store.address.state', ''),
            config('ecommerce.store.address.city', ''),
            '',
            config('ecommerce.store.address.zip', ''),
            '',
            config('ecommerce.store.address.address', ''),
            config('ecommerce.store.address.address2', ''),
            config('ecommerce.store.address.organization', ''),
        );

        $this->vatId = config('ecommerce.store.address.vat_id', '');
    }

    public function withVatId(string $string): self
    {
        $new        = clone $this;
        $new->vatId = $string;

        return $new;
    }

    public function vatId(): string
    {
        return $this->vatId();
    }
}
