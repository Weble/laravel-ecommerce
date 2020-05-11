<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;

interface DiscountInterface
{
    public function type(): DiscountType;

    public function calculateValue(Money $price): Money;

    public function target(): DiscountTarget;
}
