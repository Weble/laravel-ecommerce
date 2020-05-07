<?php


namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;
use CommerceGuys\Tax\Model\TaxTypeInterface;
use CommerceGuys\Tax\TaxableInterface;
use Illuminate\Support\Collection;

interface Purchasable extends TaxableInterface
{
    public function cartPrice(?Collection $cartAttributes = null): Money;

    public function cartTaxType(): TaxTypeInterface;
}
