<?php

namespace Weble\LaravelEcommerce\Address;

use CommerceGuys\Addressing\AbstractEnum;

class AddressType extends AbstractEnum
{
    const BILLING = 'billing';
    const SHIPPING = 'shipping';

    /**
     * Gets the default value.
     *
     * @return string The default value.
     */
    public static function getDefault()
    {
        return static::BILLING;
    }
}
