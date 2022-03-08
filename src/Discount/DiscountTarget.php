<?php

namespace Weble\LaravelEcommerce\Discount;

enum DiscountTarget: string
{
    case Item = "item";
    case Items = "items";
    case Subtotal = "subtotal";
    case Shipping = "shipping";
}
