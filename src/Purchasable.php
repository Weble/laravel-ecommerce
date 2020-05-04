<?php


namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;

interface Purchasable
{
    public function cartPrice(): Money;
}
