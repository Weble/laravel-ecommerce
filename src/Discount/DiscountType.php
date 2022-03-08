<?php

namespace Weble\LaravelEcommerce\Discount;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Value = 'value';
}
