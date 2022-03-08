<?php

namespace Weble\LaravelEcommerce\Address;

enum AddressType: string
{
    case Billing = "billing";
    case Shipping = "shipping";
}
