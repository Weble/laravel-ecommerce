<?php

namespace Weble\LaravelEcommerce\Order;

use Omnipay\Common\CreditCard;
use Weble\LaravelEcommerce\Customer\Customer;

class PaymentData extends CreditCard
{
    public static function fromCustomer(Customer $customer): self
    {
        return new static([
            'firstName'        => $customer->billingAddress->getGivenName(),
            'lastName'         => $customer->billingAddress->getFamilyName(),
            'billingAddress1'  => $customer->billingAddress->street,
            'billingCity'      => $customer->billingAddress->city,
            'billingPostcode'  => $customer->billingAddress->zip,
            'billingState'     => $customer->billingAddress->state,
            'billingCountry'   => $customer->billingAddress->country,
            'shippingAddress1' => $customer->shippingAddress->street,
            'shippingCity'     => $customer->shippingAddress->city,
            'shippingPostcode' => $customer->shippingAddress->zip,
            'shippingState'    => $customer->shippingAddress->state,
            'shippingCountry'  => $customer->shippingAddress->country,
            'company'          => $customer->billingAddress->company,
            'email'            => $customer->email,
        ]);
    }
}
