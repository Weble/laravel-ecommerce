<?php

namespace Weble\LaravelEcommerce\Tests\mocks;

use Cknow\Money\Money;
use CommerceGuys\Tax\Model\TaxTypeInterface;
use CommerceGuys\Tax\Repository\TaxTypeRepository;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Purchasable;

class Product implements Purchasable
{
    private int $id;
    private Money $price;

    public function __construct(int $id, Money $price)
    {
        $this->id = $id;
        $this->price = $price;
    }

    public function getKey()
    {
        return $this->id;
    }

    public function cartTaxType(): TaxTypeInterface
    {
        return (new TaxTypeRepository())->get('it_vat_standard');
    }

    public function isPhysical()
    {
        return true;
    }

    public function cartPrice(?Collection $cartAttributes = null): Money
    {
        return $this->price;
    }
}