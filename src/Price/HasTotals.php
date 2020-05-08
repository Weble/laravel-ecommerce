<?php

namespace Weble\LaravelEcommerce\Price;

use Cknow\Money\Money;

interface HasTotals
{
    public function subTotal(): Money;

    public function tax(): Money;

    public function total(): Money;
}
