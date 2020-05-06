<?php


namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;
use Illuminate\Support\Collection;

interface Purchasable
{
    public function cartId();

    public function cartPrice(?Collection $cartAttributes = null): Money;
}
